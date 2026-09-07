<?php

namespace Tests\Feature;

use App\Livewire\Storefront\Auth\LoginPage;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    public function test_login_page_renders_for_guest(): void
    {
        $this->get('/giris')->assertOk()->assertSee('Hoş geldiniz');
    }

    public function test_login_with_valid_credentials_redirects_to_account(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/hesabim');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials_shows_error(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_page_redirects_to_account_when_already_authenticated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/giris')
            ->assertRedirect('/hesabim');
    }
}
