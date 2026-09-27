<?php

namespace App\Livewire\Storefront;

use App\Models\ContactMessage;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class ContactPage extends Component
{
    use WithRateLimiting;

    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    /** Honeypot alanı: insanlar görmez, botlar doldurur. */
    public string $website = '';

    public bool $sent = false;

    public function submitMessage(): void
    {
        // Honeypot doluysa istek bir bottan geliyordur: kayıt yapılmaz,
        // bota ele vermemek için sessizce "başarılı" davranışı gösterilir.
        if (filled($this->website)) {
            $this->reset('name', 'email', 'subject', 'message', 'website');
            $this->sent = true;

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
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email:filter', 'max:255'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'name.required' => 'Adınızı girin.',
            'name.max' => 'Adınız çok uzun.',
            'email.required' => 'E-posta adresi gerekli.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.max' => 'E-posta adresi çok uzun.',
            'subject.required' => 'Konu başlığı gerekli.',
            'subject.max' => 'Konu başlığı çok uzun.',
            'message.required' => 'Mesajınızı yazın.',
            'message.min' => 'Mesajınız en az 10 karakter olmalı.',
            'message.max' => 'Mesajınız çok uzun.',
        ]);

        ContactMessage::create([
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'ip_address' => request()->ip(),
        ]);

        $this->reset('name', 'email', 'subject', 'message', 'website');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.storefront.contact-page')->layoutData([
            'title' => 'İletişim',
            'description' => 'ExportDepo ile iletişime geçin: soru, öneri ve talepleriniz için form, adres ve iletişim bilgileri.',
        ]);
    }
}
