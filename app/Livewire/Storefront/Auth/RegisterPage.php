<?php

namespace App\Livewire\Storefront\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\FieldTypes\Text;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;

#[Layout('layouts.storefront')]
class RegisterPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        // Giriş yapılmış kullanıcıyı 'guest' middleware /hesabim'a yönlendirir.
        // Mount'tan redirect dönmek page component'te 500 ürettiği için kaldırıldı.
    }

    public function register()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'password.confirmed' => 'Şifre tekrarı eşleşmiyor.',
            'password.min' => 'Şifre en az 8 karakter olmalı.',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
            'password' => $this->password,
        ]);

        $this->attachCustomer($user);

        Auth::login($user);
        session()->regenerate();

        return redirect('/hesabim');
    }

    protected function attachCustomer(User $user): void
    {
        $customerGroup = CustomerGroup::where('default', true)->first()
            ?? CustomerGroup::first()
            ?? CustomerGroup::create(['name' => 'General', 'handle' => 'general', 'default' => true]);

        $customer = Customer::create([
            'title' => null,
            'first_name' => Str::before($user->name, ' ') ?: $user->name,
            'last_name' => Str::after($user->name, ' ') ?: '-',
            'account_ref' => 'CUST-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'attribute_data' => collect([
                'name' => new Text($user->name),
            ]),
        ]);

        $customer->customerGroups()->attach($customerGroup->id);
        $customer->users()->attach($user->id);
    }

    public function render()
    {
        return view('livewire.storefront.auth.register-page');
    }
}
