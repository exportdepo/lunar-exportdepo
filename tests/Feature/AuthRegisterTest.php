<?php

namespace Tests\Feature;

use App\Livewire\Storefront\Auth\RegisterPage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthRegisterTest extends TestCase
{
    public function test_register_page_renders_for_guest(): void
    {
        $this->get('/kayit')->assertOk()->assertSee('Hesap Oluşturun');
    }

    public function test_register_with_valid_data_creates_user_and_customer(): void
    {
        $email = 'newuser-' . uniqid() . '@example.com';

        Livewire::test(RegisterPage::class)
            ->set('name', 'Test Kullanıcı')
            ->set('email', $email)
            ->set('phone', '05551234567')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect('/hesabim');

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);
        $this->assertSame('Test Kullanıcı', $user->name);
        $this->assertSame('05551234567', $user->phone);
        $this->assertTrue(Hash::check('password123', $user->password));

        $this->assertAuthenticatedAs($user);
        $this->assertCount(1, $user->customers, 'User should be linked to a Lunar Customer');
    }

    public function test_register_with_password_mismatch_fails(): void
    {
        $email = 'mismatch-' . uniqid() . '@example.com';

        Livewire::test(RegisterPage::class)
            ->set('name', 'Test Kullanıcı')
            ->set('email', $email)
            ->set('password', 'password123')
            ->set('password_confirmation', 'different')
            ->call('register')
            ->assertHasErrors(['password']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    public function test_register_with_existing_email_fails(): void
    {
        $email = 'taken-' . uniqid() . '@example.com';
        User::factory()->create(['email' => $email]);

        Livewire::test(RegisterPage::class)
            ->set('name', 'Test')
            ->set('email', $email)
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('register')
            ->assertHasErrors(['email']);
    }
}
