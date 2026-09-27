<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CollectionPage;
use App\Livewire\Storefront\HomePage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Lunar\Models\Collection;
use Tests\Concerns\CreatesStorefrontProduct;
use Tests\TestCase;

/**
 * Ana sayfa ve koleksiyon (PLP) listelerindeki "Sepete Ekle" akışını doğrular.
 */
class AddToCartFromListTest extends TestCase
{
    use CreatesStorefrontProduct;

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
}
