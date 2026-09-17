<?php

namespace Tests\Feature;

use App\Livewire\Storefront\Auth\ForgotPasswordPage;
use App\Livewire\Storefront\Auth\LoginPage;
use App\Livewire\Storefront\Auth\RegisterPage;
use App\Livewire\Storefront\Auth\ResetPasswordPage;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerEmailNormalizationTest extends TestCase
{
    public function test_registration_normalizes_email_and_customer_can_log_in_again(): void
    {
        Livewire::test(RegisterPage::class)
            ->set('name', 'Test Customer')
            ->set('email', '  Customer@Example.COM  ')
            ->set('password', 'Customer-password-123')
            ->set('password_confirmation', 'Customer-password-123')
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect('/hesabim');

        $user = User::where('email', 'customer@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Customer-password-123', $user->password));
        Auth::logout();

        Livewire::test(LoginPage::class)
            ->set('email', '  CUSTOMER@EXAMPLE.COM  ')
            ->set('password', 'Customer-password-123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/hesabim');

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_login_normalizes_email_but_does_not_trim_password(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => '  Customer-password-123  ',
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', '  CUSTOMER@EXAMPLE.COM  ')
            ->set('password', '  Customer-password-123  ')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/hesabim');

        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_registration_rejects_case_variant_of_existing_email(): void
    {
        User::factory()->create(['email' => 'customer@example.com']);

        Livewire::test(RegisterPage::class)
            ->set('name', 'Another Customer')
            ->set('email', ' CUSTOMER@EXAMPLE.COM ')
            ->set('password', 'Customer-password-123')
            ->set('password_confirmation', 'Customer-password-123')
            ->call('register')
            ->assertHasErrors(['email' => 'unique']);

        $this->assertSame(1, User::count());
        $this->assertGuest('web');
    }

    public function test_user_model_normalizes_email_on_create_and_update(): void
    {
        $user = User::factory()->create(['email' => ' Customer@Example.COM ']);
        $this->assertSame('customer@example.com', $user->fresh()->email);

        $user->update(['email' => ' Updated@Example.COM ']);
        $this->assertSame('updated@example.com', $user->fresh()->email);
    }

    public function test_forgot_password_normalizes_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'customer@example.com']);

        Livewire::test(ForgotPasswordPage::class)
            ->set('email', ' CUSTOMER@EXAMPLE.COM ')
            ->call('sendResetLink')
            ->assertHasNoErrors()
            ->assertSet('emailSent', true);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_normalizes_email_and_new_password_allows_login(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $token = Password::broker()->createToken($user);

        Livewire::test(ResetPasswordPage::class, ['token' => $token])
            ->set('email', ' CUSTOMER@EXAMPLE.COM ')
            ->set('password', 'New-customer-password-123')
            ->set('password_confirmation', 'New-customer-password-123')
            ->call('resetPassword')
            ->assertHasNoErrors()
            ->assertSet('resetSuccess', true);

        Livewire::test(LoginPage::class)
            ->set('email', ' CUSTOMER@EXAMPLE.COM ')
            ->set('password', 'New-customer-password-123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/hesabim');

        $this->assertAuthenticatedAs($user, 'web');
    }
}
