<?php

namespace Tests\Feature;

use App\Services\DeleteTaxClasses;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Models\CartLine;
use Lunar\Models\OrderLine;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class TaxClassDeleteTest extends TestCase
{
    use SeedsLunarDefaults;

    public function test_deletes_tax_class_with_related_variants_and_dependencies(): void
    {
        $this->seedLunarDefaults();

        $taxClass = TaxClass::factory()->hasTaxRateAmounts(2)->create();

        $variant = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
        ]);

        $price = Price::factory()->create([
            'priceable_type' => ProductVariant::morphName(),
            'priceable_id' => $variant->id,
        ]);

        $cartLine = CartLine::factory()->create([
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => $variant->id,
        ]);

        // Sipariş satırları (geçmiş) korunmalı
        $orderLine = OrderLine::factory()->create([
            'purchasable_type' => ProductVariant::morphName(),
            'purchasable_id' => $variant->id,
        ]);

        app(DeleteTaxClasses::class)->handle(
            new EloquentCollection([$taxClass]),
        );

        // Vergi sınıfı ve bağlı oran tutarları silindi
        $this->assertDatabaseMissing('lunar_tax_classes', ['id' => $taxClass->id]);
        $this->assertDatabaseMissing('lunar_tax_rate_amounts', ['tax_class_id' => $taxClass->id]);

        // Bağlı ürün varyantı ve bağımlılıkları silindi
        $this->assertDatabaseMissing('lunar_product_variants', ['id' => $variant->id]);
        $this->assertDatabaseMissing('lunar_prices', [
            'priceable_type' => ProductVariant::morphName(),
            'priceable_id' => $variant->id,
        ]);
        $this->assertDatabaseMissing('lunar_cart_lines', ['id' => $cartLine->id]);

        // Sipariş geçmişi korunur
        $this->assertDatabaseHas('lunar_order_lines', ['id' => $orderLine->id]);
    }

    public function test_deletes_tax_class_without_variants(): void
    {
        $this->seedLunarDefaults();

        $taxClass = TaxClass::factory()->create();

        app(DeleteTaxClasses::class)->handle(
            new EloquentCollection([$taxClass]),
        );

        $this->assertDatabaseMissing('lunar_tax_classes', ['id' => $taxClass->id]);
    }
}