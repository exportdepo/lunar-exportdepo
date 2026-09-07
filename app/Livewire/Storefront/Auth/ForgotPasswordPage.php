<?php

namespace App\Livewire\Storefront\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.storefront')]
class ForgotPasswordPage extends Component
{
    public string $email = '';
    public bool $emailSent = false;

    public function sendResetLink()
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->emailSent = true;
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.storefront.auth.forgot-password-page');
    }
}
