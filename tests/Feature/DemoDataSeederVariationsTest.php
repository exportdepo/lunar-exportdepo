<?php

namespace Tests\Feature;

use Database\Seeders\DemoDataSeeder;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductVariant;
use Tests\TestCase;

/**
 * Demo verisinin renk / beden / model gibi varyasyonları doğru ürettiğini doğrular.
 */
class DemoDataSeederVariationsTest extends TestCase
{
    public function test_seeder_creates_colour_and_size_variations_for_apparel(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('premium-pamuk-tisort');

        $this->assertNotNull($product, 'Varyasyonlu ürün seed edilmemiş.');
        $this->assertSame(2, $product->productOptions()->count(), 'Renk ve Beden seçeneği bekleniyordu.');

        // 4 renk × 5 beden = 20 kombinasyon varyantı.
        $this->assertSame(20, $product->variants()->count());

        // Renk seçeneğinin değerleri vitrin swatch'ı için hex kodu taşır.
        $renk = $product->productOptions()->where('handle', 'premium-pamuk-tisort-renk')->first();
        $this->assertNotNull($renk);
        $this->assertSame('Renk', $renk->translate('name'));
        $this->assertSame(4, $renk->values()->count());
        $this->assertSame('#111827', $renk->values()->first()->meta['hex']);

        // Bedenler sıralı ve doğru adlarla üretilir.
        $beden = $product->productOptions()->where('handle', 'premium-pamuk-tisort-beden')->first();
        $this->assertNotNull($beden);
        $this->assertSame(
            ['S', 'M', 'L', 'XL', '2XL'],
            $beden->values()->orderBy('position')->get()->map(fn (ProductOptionValue $value) => $value->translate('name'))->all()
        );

        // Her varyant bir renk ve bir beden değerine bağlıdır.
        foreach ($product->variants as $variant) {
            $this->assertSame(2, $variant->values()->count());
            $this->assertNotNull($variant->prices()->first(), 'Her varyantın fiyatı olmalı.');
        }
    }

    public function test_seeder_applies_price_delta_and_marks_out_of_stock_combinations(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('premium-pamuk-tisort');

        // Baz fiyat 349 TL; Siyah/S fark yok, Siyah/XL +10 TL.
        $base = $product->variants()->where('sku', 'LUN-PREMIUM-PAMUK-TISORT-SIYAH-S')->first();
        $xl = $product->variants()->where('sku', 'LUN-PREMIUM-PAMUK-TISORT-SIYAH-XL')->first();
        $outOfStock = $product->variants()->where('sku', 'LUN-PREMIUM-PAMUK-TISORT-SIYAH-2XL')->first();

        $this->assertNotNull($base);
        $this->assertNotNull($xl);
        $this->assertNotNull($outOfStock);

        $this->assertSame(34900, $base->prices()->first()->price->value);
        $this->assertSame(35900, $xl->prices()->first()->price->value);

        // 2XL kombinasyonu stok dışı işaretlenir; vitrinde soluk/çizili görünür.
        $this->assertSame(0, $outOfStock->stock);
    }

    public function test_seeder_variations_are_idempotent(): void
    {
        $this->seed(DemoDataSeeder::class);

        $variantCount = ProductVariant::count();
        $optionCount = ProductOption::count();
        $valueCount = ProductOptionValue::count();

        $this->seed(DemoDataSeeder::class);

        $this->assertSame($variantCount, ProductVariant::count(), 'Tekrar seed varyant çoğaltmamalı.');
        $this->assertSame($optionCount, ProductOption::count(), 'Tekrar seed seçenek çoğaltmamalı.');
        $this->assertSame($valueCount, ProductOptionValue::count(), 'Tekrar seed seçenek değeri çoğaltmamalı.');
    }

    public function test_seeder_leaves_simple_products_with_a_single_variant(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('lunarbook-pro-14');

        $this->assertNotNull($product);
        $this->assertSame(0, $product->productOptions()->count());
        $this->assertSame(1, $product->variants()->count());
    }

    private function productBySlug(string $slug): ?Product
    {
        return Product::whereHas('urls', fn ($query) => $query->where('slug', $slug))->first();
    }
}