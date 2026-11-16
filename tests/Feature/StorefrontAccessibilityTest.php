<?php

namespace Tests\Feature;

use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Vitrin sayfasının erişilebilirlik sözleşmesini doğrular.
 *
 * Kapsam: klavye kullanıcıları için atlama bağlantısı (WCAG 2.4.1) ve
 * modal katmanların (arama, sepet çekmecesi) diyalog anlamı ile odak
 * geri yükleme hedefleri (WCAG 2.4.3).
 */
class StorefrontAccessibilityTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        // Header'daki `livewire:storefront.cart-indicator` bileşeni Lunar'ın
        // varsayılan para birimi / dil / kanal kayıtlarına bağlıdır.
        $this->seedLunarDefaults();
    }

    public function test_storefront_renders_skip_link_before_the_header(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('href="#main-content"', false);
        $response->assertSeeInOrder(
            ['href="#main-content"', '<header'],
            escape: false,
        );
    }

    public function test_skip_link_target_is_focusable(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="main-content"', false)
            ->assertSee('tabindex="-1"', false);
    }

    public function test_search_modal_exposes_dialog_semantics(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="header-search-modal"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('aria-controls="header-search-modal"', false);
    }

    public function test_cart_drawer_exposes_dialog_semantics(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="cart-drawer"', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('aria-labelledby="cart-drawer-title"', false);
    }

    public function test_overlay_triggers_expose_focus_restoration_targets(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="search-trigger-button"', false)
            ->assertSee('id="mobile-menu-trigger"', false)
            ->assertSee('id="cart-trigger-button"', false);
    }

    public function test_mobile_menu_is_controlled_by_its_trigger(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="mobile-menu"', false)
            ->assertSee('aria-controls="mobile-menu"', false)
            ->assertSee(':aria-expanded="mobileOpen"', false);
    }

    /**
     * Odak tuzağı ve odak geri yükleme, Alpine `x-ref` bağlantılarıyla
     * kurulur: modal kabukları kendi kapsamlarına, tetikleyiciler geri
     * yükleme hedeflerine bağlanır.
     */
    public function test_overlays_expose_alpine_focus_wiring(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('x-data="header"', false)
            ->assertSee('x-data="cartDrawer"', false)
            ->assertSee('x-ref="searchModal"', false)
            ->assertSee('x-ref="searchInput"', false)
            ->assertSee('x-ref="searchTrigger"', false)
            ->assertSee('x-ref="mobileMenu"', false)
            ->assertSee('x-ref="mobileTrigger"', false)
            ->assertSee('x-ref="cartTrigger"', false)
            ->assertSee('x-ref="panel"', false);
    }

    /**
     * `x-data` nesnesi `init()` metodu içerdiğinde Alpine bunu kendisi çağırır;
     * ayrıca `x-init="init()"` eklenirse aynı yayınlayıcı iki kez kurulur ve
     * odak tuzağı dinleyicisi kopyalanır.
     */
    public function test_overlays_do_not_double_initialise_their_focus_trap(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('x-init="init()"', false);
    }
}
