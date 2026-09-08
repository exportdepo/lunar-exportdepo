<?php

namespace Tests\Feature;

use App\Livewire\Storefront\ProductPage;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    protected function tearDown(): void
    {
        CartSession::forget();

        parent::tearDown();
    }

    public function test_product_page_renders_variant_selector_for_products_with_multiple_variants(): void
    {
        $product = $this->productWithMultipleVariants();

        Livewire::test(ProductPage::class, ['slug' => $this->productSlug($product)])
            ->assertViewHas('variants', fn (array $variants): bool => count($variants) > 1)
            ->assertSet('selectedVariantId', $product->variants->first()->id);
    }

    public function test_selecting_a_variant_updates_price_stock_and_cart_variant(): void
    {
        $product = $this->productWithMultipleVariants();
        $firstVariant = $product->variants->first();
        $selectedVariant = $product->variants->skip(1)->first();
        $component = Livewire::test(ProductPage::class, ['slug' => $this->productSlug($product)]);

        $component->set('selectedVariantId', $selectedVariant->id)
            ->assertSet('product.variant_id', $selectedVariant->id)
            ->assertSet('product.stock', $selectedVariant->stock)
            ->assertSet('product.price', $selectedVariant->prices->first()?->price?->value);

        $component->call('addToCart');

        $line = CartSession::current()->lines->first();
        $this->assertSame($selectedVariant->id, $line->purchasable_id);
        $this->assertSame((new $selectedVariant)->getMorphClass(), $line->purchasable_type);
        $this->assertSame(1, $line->quantity);
        $this->assertNotSame($firstVariant->id, $selectedVariant->id);
    }

    private function productWithMultipleVariants(): Product
    {
        $language = Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);
        $currency = Currency::factory()->create([
            'code' => 'TRY'.random_int(100, 999),
            'default' => true,
        ]);
        $product = Product::factory()->create(['status' => 'published']);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => 'test-variant-product-'.uniqid(),
            'default' => true,
        ]);

        foreach ([100, 200] as $index => $price) {
            $variant = $product->variants()->create([
                'tax_class_id' => \Lunar\Models\TaxClass::factory()->create()->id,
                'sku' => 'TEST-VARIANT-'.($index + 1).'-'.uniqid(),
                'stock' => 5 + $index,
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'shippable' => true,
                'purchasable' => 'in_stock',
            ]);
            $variant->prices()->create([
                'currency_id' => $currency->id,
                'price' => $price,
                'min_quantity' => 1,
            ]);
        }

        return $product->fresh(['variants.prices.currency', 'variants.values', 'urls']);
    }

    private function productSlug(Product $product): string
    {
        return (string) $product->urls->first()->slug;
    }
}
