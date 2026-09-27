<?php

namespace Tests\Feature;

use App\Livewire\Storefront\NewsletterSubscription;
use App\Models\NewsletterSubscriber;
use Livewire\Livewire;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // WithRateLimiting sayaçları cache'te testler arasında taşınabilir;
        // her test temiz sayaçla başlasın diye cache temizlenir.
        cache()->flush();
    }

    public function test_valid_email_subscribes(): void
    {
        Livewire::test(NewsletterSubscription::class)
            ->set('email', 'ornek@exportdepo.test')
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Abone oldunuz! Fırsatlardan ilk siz haberdar olacaksınız.');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'ornek@exportdepo.test',
        ]);
    }

    public function test_invalid_email_fails_validation(): void
    {
        Livewire::test(NewsletterSubscription::class)
            ->set('email', 'gecersiz')
            ->call('subscribe')
            ->assertHasErrors(['email'])
            ->assertDontSee('Abone oldunuz');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_duplicate_email_is_not_duplicated(): void
    {
        NewsletterSubscriber::create(['email' => 'ornek@exportdepo.test']);

        Livewire::test(NewsletterSubscription::class)
            ->set('email', 'ornek@exportdepo.test')
            ->call('subscribe')
            ->assertHasNoErrors()
            ->assertSet('successMessage', 'Bu e-posta zaten abone listemizde.');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_honeypot_filled_creates_no_record(): void
    {
        // Bot honeypot alanını doldurursa kayıt oluşmaz; bota başarı gösterilir.
        Livewire::test(NewsletterSubscription::class)
            ->set('website', 'http://spam.example')
            ->set('email', 'bot@spam.example')
            ->call('subscribe')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_rate_limit_blocks_after_five_attempts(): void
    {
        $component = Livewire::test(NewsletterSubscription::class);

        foreach (range(1, 5) as $attempt) {
            $component
                ->set('email', "kullanici{$attempt}@exportdepo.test")
                ->call('subscribe')
                ->assertHasNoErrors();
        }

        $component
            ->set('email', 'altinci@exportdepo.test')
            ->call('subscribe')
            ->assertHasErrors(['email']);

        // İlk 5 kayıt oluşmalı, 6. deneme kayıt oluşturmamalı.
        $this->assertDatabaseCount('newsletter_subscribers', 5);
        $this->assertDatabaseMissing('newsletter_subscribers', [
            'email' => 'altinci@exportdepo.test',
        ]);
    }
}
