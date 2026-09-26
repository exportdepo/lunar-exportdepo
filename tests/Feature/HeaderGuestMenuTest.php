<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Header'daki misafir üyelik menüsünü doğrular.
 *
 * Giriş yapmamış ziyaretçide "Giriş Yap" ve "Üye Ol" butonları tek bir
 * kullanıcı ikonu açılır menüsünde, "Kayıt ol" başlığının altında toplanır.
 * Giriş yapmış kullanıcıda ise bu blok hiç render edilmez.
 */
class HeaderGuestMenuTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        // Header'daki `livewire:storefront.cart-indicator` bileşeni Lunar'ın
        // varsayılan para birimi / dil / kanal kayıtlarına bağlıdır.
        $this->seedLunarDefaults();
    }

    public function test_guest_header_shows_register_heading_with_auth_actions(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Kayıt ol')
            ->assertSee('Üye Ol')
            ->assertSee('Giriş Yap')
            ->assertSee('guest-auth-dropdown')
            ->assertSee(url('/kayit'), false)
            ->assertSee(url('/giris'), false);
    }

    public function test_authenticated_header_hides_guest_menu(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertDontSee('guest-auth-dropdown')
            ->assertSee('user-dropdown');
    }
}
