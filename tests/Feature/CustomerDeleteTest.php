<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DeleteCustomers;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Lunar\Models\Cart;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class CustomerDeleteTest extends TestCase
{
    use SeedsLunarDefaults;

    public function test_deletes_customer_with_orders_carts_addresses_and_pivots(): void
    {
        $this->seedLunarDefaults();

        $customer = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();

        $user = User::factory()->create();
        $customer->users()->sync([$user->id]);

        $group = CustomerGroup::factory()->create();
        $customer->customerGroups()->sync([$group->id]);

        // Adres satırı doğrudan eklenir (Lunar AddressFactory, faker
        // yerel ayarı tr_TR olduğunda "secondaryAddress" formatı hatası üretir).
        $addressId = DB::table('lunar_addresses')->insertGetId([
            'customer_id' => $customer->id,
            'first_name' => 'Ad',
            'last_name' => 'Soyad',
            'line_one' => 'Adres Satırı 1',
            'city' => 'İstanbul',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sipariş (geçmiş) korunmalı, müşteri referansı null'a çekilmeli
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'currency_code' => 'TRY',
        ]);

        $cart = Cart::factory()->create([
            'customer_id' => $customer->id,
            'currency_id' => \Lunar\Models\Currency::query()->where('default', true)->firstOrFail()->id,
            'channel_id' => \Lunar\Models\Channel::query()->where('default', true)->firstOrFail()->id,
        ]);

        app(DeleteCustomers::class)->handle(
            new EloquentCollection([$customer]),
        );

        // Müşteri ve bağlantıları silindi
        $this->assertDatabaseMissing('lunar_customers', ['id' => $customer->id]);
        $this->assertDatabaseMissing('lunar_customer_user', ['customer_id' => $customer->id]);
        $this->assertDatabaseMissing('lunar_customer_customer_group', ['customer_id' => $customer->id]);
        $this->assertDatabaseMissing('lunar_addresses', ['id' => $addressId]);

        // Sipariş geçmişi korunur, müşteri referansı null'a çekilir
        $this->assertDatabaseHas('lunar_orders', ['id' => $order->id]);
        $this->assertNull(DB::table('lunar_orders')->where('id', $order->id)->value('customer_id'));

        // Sepet korunur, referans null'a çekilir
        $this->assertDatabaseHas('lunar_carts', ['id' => $cart->id]);
        $this->assertNull(DB::table('lunar_carts')->where('id', $cart->id)->value('customer_id'));

        // Diğer müşteri etkilenmez
        $this->assertDatabaseHas('lunar_customers', ['id' => $otherCustomer->id]);
    }

    public function test_deletes_customer_without_relations(): void
    {
        $this->seedLunarDefaults();

        $customer = Customer::factory()->create();

        app(DeleteCustomers::class)->handle(
            new EloquentCollection([$customer]),
        );

        $this->assertDatabaseMissing('lunar_customers', ['id' => $customer->id]);
    }
}
