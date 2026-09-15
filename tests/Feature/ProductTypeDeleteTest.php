<?php

namespace Tests\Feature;

use App\Services\DeleteProductTypes;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Models\CartLine;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class ProductTypeDeleteTest extends TestCase
{
    use SeedsLunarDefaults;

    public function test_deletes_product_type_with_related_products_and_dependencies(): void
    {
        $this->seedLunarDefaults();

        $stockType = ProductType::factory()->create(['name' => 'Stock']);
        $otherType = ProductType::factory()->create(['name' => 'Silinecek']);

        // "Stock" türüne bağlı ürün (korunmalı)
        $stockProduct = Product::factory()->create(['product_type_id' => $stockType->id]);
        $stockVariant = ProductVariant::factory()->create([
            'product_id' => $stockProduct->id,
            'tax_class_id' => TaxClass::factory(),
        ]);

        // Silinecek türe bağlı ürün + bağımlılıkları
        $product = Product::factory()->create(['product_type_id' => $otherType->id]);
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
            'tax_class_id' => TaxClass::factory(),
        ]);

        $price = Price::factory()->create([
            'priceable_type' => ProductVariant::morphName(),
            'priceable_id' => $variant->id,
        ]);

        $cartLine = CartLine::factory()->create([
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => $variant->id,
        ]);

        $channel = \Lunar\Models\Channel::first();
        $product->channels()->syncWithoutDetaching([$channel->id]);

        $language = Language::first();
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => 'test-product-to-delete',
            'default' => true,
        ]);

        app(DeleteProductTypes::class)->handle(
            new EloquentCollection([$otherType]),
        );

        // Ürün türü ve bağlı kayıtları silindi
        $this->assertDatabaseMissing('lunar_product_types', ['id' => $otherType->id]);
        $this->assertDatabaseMissing('lunar_products', ['id' => $product->id]);
        $this->assertDatabaseMissing('lunar_product_variants', ['id' => $variant->id]);
        $this->assertDatabaseMissing('lunar_prices', [
            'priceable_type' => ProductVariant::morphName(),
            'priceable_id' => $variant->id,
        ]);
        $this->assertDatabaseMissing('lunar_cart_lines', ['id' => $cartLine->id]);
        $this->assertDatabaseMissing('lunar_channelables', [
            'channelable_type' => Product::morphName(),
            'channelable_id' => $product->id,
        ]);
        $this->assertDatabaseMissing('lunar_urls', [
            'element_type' => Product::morphName(),
            'element_id' => $product->id,
        ]);

        // "Stock" türü ve ürünleri korunur
        $this->assertDatabaseHas('lunar_product_types', ['id' => $stockType->id]);
        $this->assertDatabaseHas('lunar_products', ['id' => $stockProduct->id]);
        $this->assertDatabaseHas('lunar_product_variants', ['id' => $stockVariant->id]);
    }

    public function test_deletes_product_type_without_products(): void
    {
        $this->seedLunarDefaults();

        $type = ProductType::factory()->create();

        app(DeleteProductTypes::class)->handle(
            new EloquentCollection([$type]),
        );

        $this->assertDatabaseMissing('lunar_product_types', ['id' => $type->id]);
    }

    public function test_deletes_soft_deleted_products_of_the_type(): void
    {
        $this->seedLunarDefaults();

        $type = ProductType::factory()->create();

        $trashedProduct = Product::factory()->create(['product_type_id' => $type->id]);
        $trashedProduct->delete(); // soft delete — satır kalır

        $this->assertDatabaseHas('lunar_products', ['id' => $trashedProduct->id]);

        app(DeleteProductTypes::class)->handle(
            new EloquentCollection([$type]),
        );

        // Soft-deleted ürün de kalıcı olarak silindi ve tür silindi
        $this->assertDatabaseMissing('lunar_products', ['id' => $trashedProduct->id]);
        $this->assertDatabaseMissing('lunar_product_types', ['id' => $type->id]);
    }
}