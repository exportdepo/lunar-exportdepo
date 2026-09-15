<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Lunar\Models\TaxClass;

/**
 * Vergi sınıflarını, sınıfa bağlı ürün varyantları (ve FK bağlantıları)
 * ile birlikte kalıcı olarak siler.
 *
 * Paneldeki varsayılan silme akışı, `lunar_product_variants.tax_class_id`
 * FK kısıtı (NOT NULL + constrained) nedeniyle bağlı varyantı olan bir
 * sınıfın silinmesini engelliyordu. Bu servis silme öncesi bağları
 * temizleyip sınıfı siler; sipariş geçmişine (order_lines) dokunmaz.
 */
class DeleteTaxClasses
{
    /**
     * @param  EloquentCollection<int, TaxClass>  $taxClasses
     */
    public function handle(EloquentCollection $taxClasses): void
    {
        DB::transaction(function () use ($taxClasses): void {
            foreach ($taxClasses as $taxClass) {
                $this->delete($taxClass);
            }
        });
    }

    protected function delete(TaxClass $taxClass): void
    {
        $taxClassId = $taxClass->getKey();

        $variantIds = DB::table('lunar_product_variants')
            ->where('tax_class_id', $taxClassId)
            ->pluck('id');

        if ($variantIds->isNotEmpty()) {
            // FK kısıtlı pivot kayıtları
            DB::table('lunar_product_option_value_product_variant')
                ->whereIn('variant_id', $variantIds)
                ->delete();

            DB::table('lunar_media_product_variant')
                ->whereIn('variant_id', $variantIds)
                ->delete();

            // Polimorfik fiyat kayıtları (hem model adı hem morph alias formları)
            DB::table('lunar_prices')
                ->whereIn('priceable_type', ['Lunar\Models\ProductVariant', 'product_variant'])
                ->whereIn('priceable_id', $variantIds)
                ->delete();

            // Aktif sepet satırları (sipariş geçmişine dokunulmaz)
            DB::table('lunar_cart_lines')
                ->whereIn('purchasable_type', ['Lunar\Models\ProductVariant', 'product_variant'])
                ->whereIn('purchasable_id', $variantIds)
                ->delete();

            // Ürün varyantlarının kendisi (hard delete; FK engelini kaldırır)
            DB::table('lunar_product_variants')
                ->whereIn('id', $variantIds)
                ->delete();
        }

        // Sınıfa bağlı vergi oranı tutarları (tax_class_id FK kısıtlı)
        DB::table('lunar_tax_rate_amounts')
            ->where('tax_class_id', $taxClassId)
            ->delete();

        // Vergi sınıfının kendisi
        DB::table('lunar_tax_classes')
            ->where('id', $taxClassId)
            ->delete();
    }
}