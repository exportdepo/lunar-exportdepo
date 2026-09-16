<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    /** @test */
    public function authenticated_user_can_logout_with_valid_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest('web');
    }

    /** @test */
    public function logout_with_stale_csrf_token_redirects_home_instead_of_419(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        // CSRF doğrulamasını devre dışı bırak: bayat/geçersiz token senaryosunu simüle eder.
        $response = $this->withoutMiddleware(ValidateCsrfToken::class)
            ->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest('web');
    }

    /** @test */
    public function guest_hitting_logout_is_redirected_to_login(): void
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
    }
}
