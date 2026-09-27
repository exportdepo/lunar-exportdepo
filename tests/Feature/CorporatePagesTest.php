<?php

namespace Tests\Feature;

use App\Livewire\Storefront\ContactPage;
use Livewire\Livewire;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Tests\TestCase;

/**
 * Kurumsal sayfaları doğrular: Hakkımızda, İletişim, Gizlilik/KVKK,
 * İade & Değişim ve SSS rotaları; iletişim formunun doğrulama,
 * honeypot ve rate limit davranışları.
 */
class CorporatePagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // WithRateLimiting sayaçları cache'te testler arasında taşınabilir;
        // her test temiz sayaçla başlasın diye cache temizlenir.
        cache()->flush();

        // Header/footer bileşenleri (CartIndicator vb.) için Lunar varsayılanları.
        Currency::factory()->create(['code' => 'TRY', 'default' => true]);
        Language::factory()->create(['code' => 'tr', 'default' => true]);
    }

    public function test_corporate_pages_render_successfully(): void
    {
        foreach ([
            '/hakkimizda',
            '/iletisim',
            '/gizlilik',
            '/iade-degisim',
            '/sss',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_corporate_pages_show_expected_content(): void
    {
        $this->get('/hakkimizda')->assertOk()->assertSee('Misyonumuz')->assertSee('Değerlerimiz');
        $this->get('/gizlilik')->assertOk()->assertSee('Veri Sorumlusu')->assertSee('Haklarınız');
        $this->get('/iade-degisim')->assertOk()->assertSee('İade Süreci')->assertSee('Kapsam Dışı Ürünler');
        $this->get('/sss')->assertOk()->assertSee('Siparişimi nasıl takip edebilirim?');
    }

    public function test_contact_form_saves_message(): void
    {
        Livewire::test(ContactPage::class)
            ->set('name', 'Test Kullanıcı')
            ->set('email', 'test@exportdepo.test')
            ->set('subject', 'Ürün hakkında soru')
            ->set('message', 'Merhaba, ürünün stok durumu hakkında bilgi alabilir miyim?')
            ->call('submitMessage')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Test Kullanıcı',
            'email' => 'test@exportdepo.test',
            'subject' => 'Ürün hakkında soru',
            'is_read' => false,
        ]);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        Livewire::test(ContactPage::class)
            ->call('submitMessage')
            ->assertHasErrors(['name', 'email', 'subject', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_form_honeypot_blocks_bots(): void
    {
        // Bot honeypot alanını doldurursa kayıt oluşmaz; bota başarı gösterilir.
        Livewire::test(ContactPage::class)
            ->set('website', 'http://spam.example')
            ->set('name', 'Bot')
            ->set('email', 'bot@spam.example')
            ->set('subject', 'Reklam')
            ->set('message', 'Spam mesajıdır bu mesaj.')
            ->call('submitMessage')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_contact_form_rate_limited_after_five_attempts(): void
    {
        $component = Livewire::test(ContactPage::class);

        foreach (range(1, 5) as $attempt) {
            $component
                ->set('name', "Kullanıcı {$attempt}")
                ->set('email', "kullanici{$attempt}@exportdepo.test")
                ->set('subject', "Konu {$attempt}")
                ->set('message', 'Bu bir test mesajıdır, ilginiz için teşekkürler.')
                ->call('submitMessage')
                ->assertHasNoErrors();
        }

        $component
            ->set('name', 'Altıncı Kullanıcı')
            ->set('email', 'altinci@exportdepo.test')
            ->set('subject', 'Altıncı konu')
            ->set('message', 'Altıncı mesajın içeriği burada yer alıyor.')
            ->call('submitMessage')
            ->assertHasErrors(['email']);

        // İlk 5 mesaj kaydedilmiş, 6. deneme kayıt oluşturmamış olmalı.
        $this->assertDatabaseCount('contact_messages', 5);
        $this->assertDatabaseMissing('contact_messages', [
            'email' => 'altinci@exportdepo.test',
        ]);
    }
}
