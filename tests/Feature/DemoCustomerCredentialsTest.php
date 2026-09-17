<?php

namespace Tests\Feature;

use App\Livewire\Storefront\Auth\LoginPage;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Lunar\Models\CustomerGroup;
use Tests\TestCase;

class DemoCustomerCredentialsTest extends TestCase
{
    public function test_reseeding_preserves_existing_customer_passwords_and_profiles(): void
    {
        $this->withCustomerPasswords(function (DemoDataSeeder $seeder, CustomerGroup $group): void {
            $this->seedCustomers($seeder, $group);
            $customers = User::orderBy('id')->get();
            $this->assertCount(2, $customers);

            foreach ($customers as $user) {
                $user->update(['password' => 'Customer-changed-password-123', 'name' => 'Updated Customer']);
            }
            $hashes = User::pluck('password', 'id')->all();

            $this->seedCustomers($seeder, $group);

            foreach ($customers as $user) {
                $user->refresh();
                $this->assertSame($hashes[$user->id], $user->password);
                $this->assertSame('Updated Customer', $user->name);
                $this->assertCount(1, $user->customers);
            }

            $user = $customers->last();
            Livewire::test(LoginPage::class)
                ->set('email', $user->email)
                ->set('password', 'Customer-changed-password-123')
                ->call('login')
                ->assertHasNoErrors()
                ->assertRedirect('/hesabim');
            $this->assertAuthenticatedAs($user, 'web');
        });
    }

    public function test_new_customers_receive_initial_passwords(): void
    {
        $this->withCustomerPasswords(function (DemoDataSeeder $seeder, CustomerGroup $group): void {
            $this->seedCustomers($seeder, $group);
            $users = User::orderBy('id')->get();
            $this->assertCount(2, $users);
            $this->assertTrue(Hash::check('Initial-demo-password-123', $users[0]->password));
            $this->assertTrue(Hash::check('Initial-legacy-password-123', $users[1]->password));
        });
    }

    private function seedCustomers(DemoDataSeeder $seeder, CustomerGroup $group): void
    {
        // Only exercise account seeding; no product/media side effects.
        foreach (['createTestCustomer', 'ensureLegacyCustomer'] as $method) {
            (new \ReflectionMethod($seeder, $method))->invoke($seeder, $group);
        }
    }

    private function withCustomerPasswords(callable $test): void
    {
        $repository = Env::getRepository();
        $passwords = [
            'EXPORTDEPO_CUSTOMER_PASSWORD' => 'Initial-demo-password-123',
            'EXPORTDEPO_CUSTOMER2_PASSWORD' => 'Initial-legacy-password-123',
        ];
        $original = [];
        foreach ($passwords as $key => $password) {
            $original[$key] = $repository->get($key);
            $repository->set($key, $password);
        }

        try {
            $test(new DemoDataSeeder, CustomerGroup::factory()->create(['default' => true]));
        } finally {
            foreach ($original as $key => $value) {
                $value === null ? $repository->clear($key) : $repository->set($key, $value);
            }
        }
    }
}
