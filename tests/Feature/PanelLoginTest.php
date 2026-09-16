<?php

namespace Tests\Feature;

use Filament\Auth\Pages\Login;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Lunar\Admin\Models\Staff;
use Tests\TestCase;

class PanelLoginTest extends TestCase
{
    public function test_panel_login_page_renders(): void
    {
        $this->get('/exportdepo/login')->assertOk();
    }

    public function test_panel_uses_staff_guard_on_exportdepo_path(): void
    {
        $panel = Filament::getPanel('lunar');

        $this->assertSame('staff', $panel->getAuthGuard());
        $this->assertSame('exportdepo', $panel->getPath());
        $this->assertSame('session', config('auth.guards.staff.driver'));
        $this->assertSame('staff', config('auth.guards.staff.provider'));
        $this->assertSame(Staff::class, config('auth.providers.staff.model'));
    }

    public function test_staff_can_authenticate_through_the_panel_login_form(): void
    {
        $staff = Staff::factory()->create([
            'email' => 'panel-admin@exportdepo.test',
            'password' => Hash::make('password'),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('lunar'));

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $staff->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($staff, 'staff');
    }

    public function test_authenticated_staff_can_reach_the_panel_dashboard(): void
    {
        $staff = Staff::factory()->create(['admin' => true]);

        $this->actingAs($staff, 'staff')
            ->get('/exportdepo')
            ->assertOk();
    }

    public function test_guests_are_redirected_away_from_the_panel_dashboard(): void
    {
        $this->get('/exportdepo')
            ->assertRedirect(route('filament.lunar.auth.login'));
    }

    public function test_staff_cannot_authenticate_with_a_wrong_password(): void
    {
        $staff = Staff::factory()->create([
            'email' => 'panel-admin@exportdepo.test',
            'password' => Hash::make('password'),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('lunar'));

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $staff->email,
                'password' => 'wrong-password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest('staff');
    }
}
