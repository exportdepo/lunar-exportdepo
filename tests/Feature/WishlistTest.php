<?php

namespace Tests\Feature;

use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\WishlistIndicator;
use App\Livewire\Storefront\WishlistPage;
use App\Models\User;
use App\Support\WishlistManager;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use SeedsLunarDefaults;

    protected int $productId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();

        $language = Language::where('default', true)->first();

        $product = Product::factory()->create(['status' => 'published']);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => 'lunarbook-pro-14',
            'default' => true,
        ]);

        $this->productId = $product->id;
    }

    public function test_guest_can_toggle_products_in_the_session_wishlist(): void
    {
        Livewire::test(HomePage::class)
            ->call('toggle', $this->productId);

        $this->assertSame([$this->productId], session(WishlistManager::SESSION_KEY));

        Livewire::test(HomePage::class)
            ->call('toggle', $this->productId);

        $this->assertSame([], session(WishlistManager::SESSION_KEY));
    }

    public function test_wishlist_indicator_reflects_the_session_count(): void
    {
        session([WishlistManager::SESSION_KEY => [$this->productId]]);

        Livewire::test(WishlistIndicator::class)
            ->assertSet('count', 1)
            ->assertSee('İstek Listem');
    }

    public function test_wishlist_page_shows_empty_state_and_saved_products(): void
    {
        $this->get('/istek-listesi')
            ->assertOk()
            ->assertSee('İstek listeniz boş');

        session([WishlistManager::SESSION_KEY => [$this->productId]]);

        $this->get('/istek-listesi')
            ->assertOk()
            ->assertDontSee('İstek listeniz boş')
            ->assertSee('1 ürün')
            ->assertSee('/urun/');

        Livewire::test(WishlistPage::class)
            ->assertCount('items', 1)
            ->call('remove', $this->productId)
            ->assertCount('items', 0);

        $this->assertSame([], session(WishlistManager::SESSION_KEY));
    }

    public function test_storefront_pages_expose_wishlist_buttons(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('İstek listesine ekle');

        $this->get('/urun/lunarbook-pro-14')
            ->assertOk()
            ->assertSee('İstek listesine ekle');
    }

    public function test_authenticated_wishlist_is_persisted_in_the_database(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(HomePage::class)
            ->call('toggle', $this->productId);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $this->productId,
        ]);

        // Aynı ürün ikinci kez eklenirse kopya kayıt oluşmamalı.
        WishlistManager::add($this->productId);
        $this->assertSame(1, WishlistManager::count());

        Livewire::test(HomePage::class)
            ->call('toggle', $this->productId);

        $this->assertDatabaseMissing('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $this->productId,
        ]);
    }

    public function test_session_wishlist_is_merged_into_the_account_on_login(): void
    {
        $user = User::factory()->create();

        session([WishlistManager::SESSION_KEY => [$this->productId]]);

        Auth::login($user);

        $this->assertDatabaseHas('wishlist_items', [
            'user_id' => $user->id,
            'product_id' => $this->productId,
        ]);

        // Taşıma sonrası oturum listesi temizlenir.
        $this->assertNull(session(WishlistManager::SESSION_KEY));
        $this->assertSame(1, WishlistManager::count());
    }
}
