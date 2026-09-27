<?php

namespace App\Livewire\Storefront;

use App\Models\NewsletterSubscriber;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class NewsletterSubscription extends Component
{
    use WithRateLimiting;

    /** Abone e-posta adresi. */
    public string $email = '';

    /** Honeypot alanı: insanlar görmez, botlar doldurur. */
    public string $website = '';

    /** Başarı durumunda gösterilen Türkçe mesaj. */
    public string $successMessage = '';

    public function subscribe(): void
    {
        $this->successMessage = '';

        // Honeypot doluysa istek bir bottan geliyordur: kayıt yapılmaz,
        // bota ele vermemek için sessizce "başarılı" davranışı gösterilir.
        if (filled($this->website)) {
            $this->reset('email', 'website');
            $this->successMessage = 'Abone oldunuz! Fırsatlar e-postanızda.';

            return;
        }

        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'email' => 'Çok fazla deneme yaptınız. Lütfen birkaç dakika sonra tekrar deneyin.',
            ]);
        }

        $this->validate([
            'email' => ['required', 'string', 'email:filter', 'max:255'],
        ], [
            'email.required' => 'E-posta adresi gerekli.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.max' => 'E-posta adresi çok uzun.',
        ]);

        // firstOrCreate: aynı e-posta ikinci kez gönderilirse kayıt çoğaltılmaz.
        $subscriber = NewsletterSubscriber::firstOrCreate(
            ['email' => $this->email],
            ['ip_address' => request()->ip()],
        );

        $this->reset('email');

        $this->successMessage = $subscriber->wasRecentlyCreated
            ? 'Abone oldunuz! Fırsatlardan ilk siz haberdar olacaksınız.'
            : 'Bu e-posta zaten abone listemizde.';
    }

    public function render()
    {
        return view('livewire.storefront.newsletter-subscription');
    }
}
