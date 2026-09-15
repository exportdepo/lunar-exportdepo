<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\ProductPage;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\TaxZone;
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

    public function test_cart_page_uses_updated_variant_price_after_price_change(): void
    {
        $product = $this->productWithMultipleVariants();
        $variant = $product->variants->first();

        CartSession::manager()->add($variant, 1);
        $variant->prices()->first()->update(['price' => 325]);

        Livewire::test(CartPage::class)
            ->assertSet('lines.0.unit_price', 325)
            ->assertSet('total', 325);
    }

    public function test_displayed_price_matches_cart_unit_price_for_tiered_quantity(): void
    {
        $product = $this->productWithMultipleVariants();
        $variant = $product->variants->first();
        $variant->prices()->create([
            'currency_id' => $variant->prices->first()->currency_id,
            'price' => 80,
            'min_quantity' => 5,
        ]);

        $component = Livewire::test(ProductPage::class, ['slug' => $this->productSlug($product)])
            ->assertSet('product.price', 100);

        $component->set('quantity', 5)
            ->assertSet('product.price', 80);

        $component->call('addToCart');

        Livewire::test(CartPage::class)
            ->assertSet('lines.0.unit_price', 80)
            ->assertSet('total', 400);
    }

    public function test_displayed_price_matches_cart_when_customer_group_price_exists(): void
    {
        $product = $this->productWithMultipleVariants();
        $variant = $product->variants->first();
        $group = CustomerGroup::query()->where('default', true)->first();

        $variant->prices()->create([
            'currency_id' => $variant->prices->first()->currency_id,
            'customer_group_id' => $group->id,
            'price' => 70,
            'min_quantity' => 1,
        ]);

        $component = Livewire::test(ProductPage::class, ['slug' => $this->productSlug($product)])
            ->assertSet('product.price', 70);

        $component->call('addToCart');

        Livewire::test(CartPage::class)
            ->assertSet('lines.0.unit_price', 70);
    }

    private function productWithMultipleVariants(): Product
    {
        TaxZone::query()->where('default', true)->exists() || TaxZone::factory()->create();

        $language = Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);
        Currency::query()->where('default', true)->update(['default' => false]);
        $currency = Currency::factory()->create([
            'code' => 'TRY'.uniqid(),
            'default' => true,
        ]);
        $product = Product::factory()->create(['status' => 'published']);
        $channel = Channel::query()->firstOrCreate(
            ['handle' => 'web'],
            ['name' => 'Web', 'url' => config('app.url'), 'default' => true],
        );
        Channel::query()->whereKeyNot($channel->id)->where('default', true)->update(['default' => false]);
        $channel->update(['default' => true]);

        CustomerGroup::query()->where('default', true)->update(['default' => false]);
        $customerGroup = CustomerGroup::query()->firstOrCreate(
            ['handle' => 'default'],
            ['name' => 'Standard', 'default' => true],
        );
        $customerGroup->update(['default' => true]);

        $product->channels()->syncWithoutDetaching([
            $channel->id => [
                'enabled' => true,
            ],
        ]);
        $product->customerGroups()->syncWithoutDetaching([
            $customerGroup->id => [
                'enabled' => true,
                'purchasable' => true,
            ],
        ]);
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
