<?php

namespace Tests\Feature;

use App\Livewire\Storefront\Auth\LoginPage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerEmailMigrationTest extends TestCase
{
    public function test_legacy_email_is_normalized_without_changing_credentials(): void
    {
        $user = User::factory()->create(['password' => 'Legacy-password-123']);
        DB::table('users')->where('id', $user->id)->update(['email' => ' Legacy@Example.COM ']);
        $before = DB::table('users')->where('id', $user->id)->first();

        $migration = require database_path('migrations/2026_09_17_000001_normalize_customer_emails.php');
        $migration->up();
        $migration->up();
        $after = DB::table('users')->where('id', $user->id)->first();

        $this->assertSame('legacy@example.com', $after->email);
        $this->assertSame($before->password, $after->password);
        $this->assertSame($before->remember_token, $after->remember_token);
        $this->assertSame($before->updated_at, $after->updated_at);

        Livewire::test(LoginPage::class)
            ->set('email', 'LEGACY@EXAMPLE.COM')
            ->set('password', 'Legacy-password-123')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/hesabim');
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_collision_stops_migration_without_changing_any_account(): void
    {
        foreach ([' Other@Example.COM ', 'Customer@Example.COM', 'customer@example.com'] as $email) {
            $user = User::factory()->create();
            DB::table('users')->where('id', $user->id)->update(['email' => $email]);
        }
        $before = DB::table('users')->orderBy('id')->get()->toJson();
        $migration = require database_path('migrations/2026_09_17_000001_normalize_customer_emails.php');

        try {
            $migration->up();
            $this->fail('Expected duplicate normalized emails to stop migration.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('normalization collision', $exception->getMessage());
        }

        $this->assertSame($before, DB::table('users')->orderBy('id')->get()->toJson());
    }
}
