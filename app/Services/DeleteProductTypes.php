<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Lunar\Models\ProductType;

/**
 * Ürün türlerini, türüne bağlı ürünler (soft-deleted dahil) ve FK/pivot
 * bağlantılarıyla birlikte kalıcı olarak siler.
 *
 * Paneldeki varsayılan silme akışı, türüne bağlı ürünü olan türleri
 * (`lunar_products.product_type_id` NOT NULL + constrained) engelliyordu.
 * Bu servis silme öncesi tüm bağları temizler; sipariş geçmişine
 * (lunar_order_lines) dokunmaz.
 */
class DeleteProductTypes
{
    /**
     * @param  EloquentCollection<int, ProductType>  $types
     */
    public function handle(EloquentCollection $types): void
    {
        DB::transaction(function () use ($types): void {
            foreach ($types as $type) {
                $this->delete($type);
            }
        });
    }

    protected function delete(ProductType $type): void
    {
        $typeId = $type->getKey();

        // Türün mapped-attribute (attributables) bağları
        DB::table('lunar_attributables')
            ->where('attributable_type', 'product_type')
            ->where('attributable_id', $typeId)
            ->delete();

        // Bağlı ürünler (soft-deleted dahil — raw sorgu, scope uygulanmaz)
        $productIds = DB::table('lunar_products')
            ->where('product_type_id', $typeId)
            ->pluck('id');

        if ($productIds->isNotEmpty()) {
            $variantIds = DB::table('lunar_product_variants')
                ->whereIn('product_id', $productIds)
                ->pluck('id');

            if ($variantIds->isNotEmpty()) {
                // FK kısıtlı pivot kayıtları
                DB::table('lunar_product_option_value_product_variant')
                    ->whereIn('variant_id', $variantIds)
                    ->delete();

                DB::table('lunar_media_product_variant')
                    ->whereIn('variant_id', $variantIds)
                    ->delete();

                // Polimorfik fiyat/sepet kayıtları (her iki morph formatı)
                DB::table('lunar_prices')
                    ->whereIn('priceable_type', ['Lunar\Models\ProductVariant', 'product_variant'])
                    ->whereIn('priceable_id', $variantIds)
                    ->delete();

                DB::table('lunar_cart_lines')
                    ->whereIn('purchasable_type', ['Lunar\Models\ProductVariant', 'product_variant'])
                    ->whereIn('purchasable_id', $variantIds)
                    ->delete();

                // Ürün varyantları (hard delete)
                DB::table('lunar_product_variants')
                    ->whereIn('id', $variantIds)
                    ->delete();
            }

            // Ürün pivotları (FK kısıtlı)
            DB::table('lunar_collection_product')
                ->whereIn('product_id', $productIds)
                ->delete();

            DB::table('lunar_customer_group_product')
                ->whereIn('product_id', $productIds)
                ->delete();

            DB::table('lunar_product_product_option')
                ->whereIn('product_id', $productIds)
                ->delete();

            // Asosiyasyonlar (parent VEYA target)
            DB::table('lunar_product_associations')
                ->where(function ($query) use ($productIds): void {
                    $query->whereIn('product_parent_id', $productIds)
                        ->orWhereIn('product_target_id', $productIds);
                })
                ->delete();

            // Polimorfik ürün bağları (morph alias ve tam sınıf adı)
            DB::table('lunar_channelables')
                ->whereIn('channelable_type', ['Lunar\Models\Product', 'product'])
                ->whereIn('channelable_id', $productIds)
                ->delete();

            DB::table('lunar_taggables')
                ->whereIn('taggable_type', ['Lunar\Models\Product', 'product'])
                ->whereIn('taggable_id', $productIds)
                ->delete();

            DB::table('lunar_urls')
                ->whereIn('element_type', ['Lunar\Models\Product', 'product'])
                ->whereIn('element_id', $productIds)
                ->delete();

            DB::table('media')
                ->whereIn('model_type', ['Lunar\Models\Product', 'product'])
                ->whereIn('model_id', $productIds)
                ->delete();

            // Ürünler (hard delete)
            DB::table('lunar_products')
                ->whereIn('id', $productIds)
                ->delete();
        }

        // Sipariş geçmişine (lunar_order_lines) dokunulmaz.

        // Ürün türünün kendisi
        DB::table('lunar_product_types')
            ->where('id', $typeId)
            ->delete();
    }
}