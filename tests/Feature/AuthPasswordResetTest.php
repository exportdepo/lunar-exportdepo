<?php

namespace Tests\Feature;

use App\Livewire\Storefront\Auth\ForgotPasswordPage;
use App\Livewire\Storefront\Auth\ResetPasswordPage;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class AuthPasswordResetTest extends TestCase
{
    public function test_forgot_password_page_renders(): void
    {
        $this->get('/sifremi-unuttum')
            ->assertOk()
            ->assertSee('Şifremi Unuttum');
    }

    public function test_forgot_password_sends_notification_for_existing_user(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        Livewire::test(ForgotPasswordPage::class)
            ->set('email', $user->email)
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertSet('emailSent', true);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_shows_error_for_invalid_email(): void
    {
        Livewire::test(ForgotPasswordPage::class)
            ->set('email', 'not-an-email')
            ->call('sendResetLink')
            ->assertHasErrors(['email']);
    }

    public function test_reset_password_with_valid_token_changes_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::broker()->createToken($user);

        Livewire::withQueryParams(['email' => $user->email])
            ->test(ResetPasswordPage::class, ['token' => $token])
            ->set('email', $user->email)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertSet('resetSuccess', true);

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
        $this->assertFalse(Hash::check('old-password', $user->password));
    }

    public function test_reset_password_with_invalid_token_fails(): void
    {
        $user = User::factory()->create();

        Livewire::withQueryParams(['email' => $user->email])
            ->test(ResetPasswordPage::class, ['token' => 'invalid-token'])
            ->set('email', $user->email)
            ->set('password', 'new-password')
            ->set('password_confirmation', 'new-password')
            ->call('resetPassword')
            ->assertHasErrors(['email']);
    }
}
