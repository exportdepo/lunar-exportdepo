<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Str;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\FieldTypes\Text;
use Lunar\Models\Channel;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class AccountPageTest extends TestCase
{
    use SeedsLunarDefaults;

    protected ?User $testUser = null;
    protected ?Customer $testCustomer = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    protected function tearDown(): void
    {
        if ($this->testCustomer) {
            $this->testCustomer->users()->detach();
            $this->testCustomer->delete();
            $this->testCustomer = null;
        }
        if ($this->testUser) {
            $this->testUser->delete();
            $this->testUser = null;
        }
        parent::tearDown();
    }

    protected function makeUserWithCustomer(): User
    {
        $user = User::factory()->create([
            'name' => 'Test Müşteri',
            'email' => 'account-' . Str::random(8) . '@example.com',
        ]);

        $channel = Channel::first();
        $cg = CustomerGroup::where('default', true)->first() ?? CustomerGroup::first();

        $customer = Customer::create([
            'title' => null,
            'first_name' => 'Test',
            'last_name' => 'Müşteri',
            'account_ref' => 'ACCT-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'attribute_data' => collect(['name' => new Text('Test Müşteri')]),
        ]);
        if ($cg) {
            $customer->customerGroups()->attach($cg->id);
        }
        $customer->users()->attach($user->id);

        $this->testUser = $user;
        $this->testCustomer = $customer;

        return $user;
    }

    protected function createOrderForCustomer(Customer $customer, array $overrides = []): Order
    {
        $channel = Channel::first();

        return Order::create(array_merge([
            'channel_id' => $channel->id,
            'customer_id' => $customer->id,
            'new_customer' => false,
            'status' => 'payment-offline',
            'reference' => 'ACC' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'sub_total' => 10000,
            'discount_total' => 0,
            'shipping_total' => 0,
            'tax_breakdown' => new TaxBreakdown,
            'tax_total' => 0,
            'total' => 10000,
            'currency_code' => 'TRY',
            'compare_currency_code' => 'TRY',
            'exchange_rate' => 1,
            'placed_at' => now(),
        ], $overrides));
    }

    public function test_account_page_requires_auth(): void
    {
        $this->get('/hesabim')->assertRedirect('/giris');
        $this->get('/hesabim/siparislerim')->assertRedirect('/giris');
    }

    public function test_account_page_renders_for_authenticated_user(): void
    {
        $user = $this->makeUserWithCustomer();

        $this->actingAs($user)
            ->get('/hesabim')
            ->assertOk()
            ->assertSee('Hesabım')
            ->assertSee($user->email)
            ->assertSee('Hesap Bilgileri');
    }

    public function test_account_orders_page_lists_user_orders(): void
    {
        $user = $this->makeUserWithCustomer();
        $order = $this->createOrderForCustomer($this->testCustomer);

        $this->actingAs($user)
            ->get('/hesabim/siparislerim')
            ->assertOk()
            ->assertSee('#' . $order->reference);

        $this->testOrderCleanup = $order; // for tearDown via different mechanism
    }

    public function test_account_orders_page_does_not_show_other_users_orders(): void
    {
        $user1 = $this->makeUserWithCustomer();
        $order1 = $this->createOrderForCustomer($this->testCustomer);

        // Create a second user with their own customer + order
        $user2 = User::factory()->create();
        $cg = CustomerGroup::where('default', true)->first() ?? CustomerGroup::first();
        $customer2 = Customer::create([
            'title' => null,
            'first_name' => 'Other',
            'last_name' => 'User',
            'account_ref' => 'ACC2-' . str_pad((string) $user2->id, 6, '0', STR_PAD_LEFT),
        ]);
        if ($cg) {
            $customer2->customerGroups()->attach($cg->id);
        }
        $customer2->users()->attach($user2->id);

        $order2 = $this->createOrderForCustomer($customer2, ['reference' => 'OTHER' . random_int(100000, 999999)]);

        $this->actingAs($user1)
            ->get('/hesabim/siparislerim')
            ->assertOk()
            ->assertSee('#' . $order1->reference)
            ->assertDontSee('#' . $order2->reference);

        // Cleanup
        $order2->delete();
        $customer2->users()->detach();
        $customer2->delete();
        $user2->delete();
    }

    public ?Order $testOrderCleanup = null;

    public function tearDownAdditional(): void
    {
        if ($this->testOrderCleanup) {
            $this->testOrderCleanup->delete();
            $this->testOrderCleanup = null;
        }
    }
}
