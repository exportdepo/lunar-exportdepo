<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CollectionPage;
use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\ProductPage;
use App\Models\User;
use App\Support\WishlistManager;
use Livewire\Livewire;
use Lunar\Models\Collection as LunarCollection;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Uyumluluk katmanı testleri: eski snapshot'lı istemcilerin WishlistButton
 * yerine ana sayfa/koleksiyon/ürün sayfası bileşenlerine gönderdiği
 * "toggle" istekleri 500'e düşmeden gerçek istek listesi işlemini yapmalı.
 */
class WishlistCompatTest extends TestCase
{
    use SeedsLunarDefaults;

    protected int $productId;

    protected string $productSlug = 'compat-toggle-product';

    protected string $collectionSlug = 'compat-koleksiyon';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();

        $language = Language::where('default', true)->first();

        $product = Product::factory()->create(['status' => 'published']);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => $this->productSlug,
            'default' => true,
        ]);
        $this->productId = $product->id;

        $collection = LunarCollection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => $this->collectionSlug,
            'default' => true,
        ]);
        $collection->products()->attach($product->id);
    }

    public function test_guest_home_page_toggle_shim_writes_to_session(): void
    {
        Livewire::test(HomePage::class)
            ->call('toggle', $this->productId);

        $this->assertSame([$this->productId], session(WishlistManager::SESSION_KEY));
    }

    public function test_home_page_toggle_shim_adds_and_removes_products(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(HomePage::class)
            ->call('toggle', $this->productId);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $this->productId,
        ]);

        Livewire::test(HomePage::class)
            ->call('toggle', $this->productId);

        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $this->productId,
        ]);
    }

    public function test_collection_page_toggle_shim_adds_and_removes_products(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(CollectionPage::class, ['slug' => $this->collectionSlug])
            ->call('toggle', $this->productId);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $this->productId,
        ]);

        Livewire::test(CollectionPage::class, ['slug' => $this->collectionSlug])
            ->call('toggle', $this->productId);

        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $this->productId,
        ]);
    }

    public function test_product_page_toggle_shim_accepts_legacy_parameterized_call(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ProductPage::class, ['slug' => $this->productSlug])
            ->call('toggle', $this->productId);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $this->productId,
        ]);
    }

    public function test_product_page_tab_switch_does_not_leak_nested_wire_id_as_text(): void
    {
        $page = Livewire::test(ProductPage::class, ['slug' => $this->productSlug]);

        $this->assertStringNotContainsString('<wire:id', $page->html());

        $page->call('selectTab', 'reviews');

        $this->assertStringNotContainsString('<wire:id', $page->html());
    }
}
