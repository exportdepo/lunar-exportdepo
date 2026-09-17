<?php

namespace App\Livewire\Storefront\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class LoginPage extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function mount()
    {
        // Giriş yapılmış kullanıcıyı 'guest' middleware /hesabim'a yönlendirir.
        // Mount'tan redirect dönmek page component'te 500 ürettiği için kaldırıldı.
    }

    public function login()
    {
        $this->email = \App\Support\CustomerEmail::normalize($this->email);

        $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'login:' . strtolower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Çok fazla giriş denemesi. Lütfen {$seconds} saniye sonra tekrar deneyin.",
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => 'E-posta veya şifre hatalı.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        return redirect()->intended('/hesabim');
    }

    public function render()
    {
        return view('livewire.storefront.auth.login-page');
    }
}
