<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CollectionPage;
use App\Livewire\Storefront\HomePage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;
use Tests\TestCase;

/**
 * Ana sayfa ve koleksiyon (PLP) listelerindeki "Sepete Ekle" akışını doğrular.
 */
class AddToCartFromListTest extends TestCase
{
    protected function tearDown(): void
    {
        CartSession::forget();

        parent::tearDown();
    }

    public function test_home_page_add_to_cart_adds_first_available_variant(): void
    {
        $product = $this->publishedProductWithVariant(stock: 5);

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id)
            ->assertSet('cartError', '');

        $line = CartSession::current()->lines->first();

        $this->assertNotNull($line);
        $this->assertSame($product->variants->first()->id, $line->purchasable_id);
        $this->assertSame(1, $line->quantity);
    }

    public function test_collection_page_add_to_cart_adds_product_to_cart(): void
    {
        $language = $this->storefrontLanguage();
        $product = $this->publishedProductWithVariant(stock: 3, languageId: $language->id);
        $slug = 'liste-koleksiyon-'.Str::ulid();

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => $slug,
            'default' => true,
        ]);
        $collection->products()->attach($product);

        Livewire::test(CollectionPage::class, ['slug' => $slug])
            ->call('addToCart', $product->id)
            ->assertSet('cartError', '');

        $this->assertSame(
            $product->variants->first()->id,
            CartSession::current()->lines->first()->purchasable_id
        );
    }

    public function test_add_to_cart_fails_gracefully_when_product_is_out_of_stock(): void
    {
        $product = $this->publishedProductWithVariant(stock: 0);

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id)
            ->assertSet('cartError', 'Bu ürün şu anda satın alınamıyor.');

        $this->assertNull(CartSession::current());
    }

    public function test_add_to_cart_fails_for_unpublished_product(): void
    {
        $product = $this->publishedProductWithVariant(stock: 5, status: 'draft');

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id)
            ->assertSet('cartError', 'Bu ürün şu anda satın alınamıyor.');

        $this->assertNull(CartSession::current());
    }

    private function storefrontLanguage(): Language
    {
        return Language::factory()->create([
            'code' => 'tr-'.Str::ulid(),
            'default' => true,
        ]);
    }

    private function publishedProductWithVariant(int $stock = 5, string $status = 'published', ?int $languageId = null): Product
    {
        TaxZone::query()->where('default', true)->exists() || TaxZone::factory()->create();

        $languageId ??= $this->storefrontLanguage()->id;

        Currency::query()->where('default', true)->update(['default' => false]);
        $currency = Currency::factory()->create([
            'code' => 'TRY'.Str::ulid(),
            'default' => true,
        ]);

        $product = Product::factory()->create(['status' => $status]);

        $channel = Channel::query()->firstOrCreate(
            ['handle' => 'web'],
            ['name' => 'Web', 'url' => config('app.url'), 'default' => true],
        );
        $channel->update(['default' => true]);

        CustomerGroup::query()->where('default', true)->update(['default' => false]);
        $customerGroup = CustomerGroup::query()->firstOrCreate(
            ['handle' => 'default'],
            ['name' => 'Standard', 'default' => true],
        );
        $customerGroup->update(['default' => true]);

        $product->channels()->syncWithoutDetaching([$channel->id => ['enabled' => true]]);
        $product->customerGroups()->syncWithoutDetaching([
            $customerGroup->id => ['enabled' => true, 'purchasable' => true],
        ]);
        $product->urls()->create([
            'language_id' => $languageId,
            'slug' => 'liste-urun-'.Str::ulid(),
            'default' => true,
        ]);

        $variant = $product->variants()->create([
            'tax_class_id' => TaxClass::factory()->create()->id,
            'sku' => 'LIST-'.Str::ulid(),
            'stock' => $stock,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'in_stock',
        ]);
        $variant->prices()->create([
            'currency_id' => $currency->id,
            'price' => 100,
            'min_quantity' => 1,
        ]);

        return $product->fresh(['variants.prices.currency', 'variants.values', 'urls', 'channels']);
    }
}
