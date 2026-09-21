<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CheckoutPage;
use App\Livewire\Storefront\HomePage;
use App\Mail\OrderConfirmation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Facades\CartSession;
use Lunar\Facades\Payments;
use Lunar\FieldTypes\Text;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * CheckoutPage placeOrder akışını doğrular: offline (cash-in-hand) mutlu yol,
 * doğrulama hataları, boş sepet, yapılandırılmamış Stripe, başarısız ödeme
 * sürücüsü ve yetersiz stok senaryoları.
 */
class CheckoutTest extends TestCase
{
    use SeedsLunarDefaults;

    protected ?Customer $testCustomer = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();

        // CheckoutPage TR ülkesi ve varsayılan vergi sınıfı bekler; test ortamı
        // .env'den bağımsız ve deterministik olsun diye ödeme ayarlarını sabitliyoruz.
        Country::factory()->create(['iso2' => 'TR']);
        TaxClass::factory()->create(['default' => true, 'name' => 'Ürünler']);
        config([
            'lunar.payments.default' => 'cash-in-hand',
            'lunar.payments.types.stripe.secret' => null,
            'services.stripe.webhook_secret' => null,
        ]);
    }

    protected function tearDown(): void
    {
        CartSession::forget();

        parent::tearDown();
    }

    public function test_offline_checkout_places_order_and_decrements_stock(): void
    {
        Mail::fake();

        $product = $this->publishedProductWithVariant(stock: 5);
        $variant = $product->variants->first();
        $initialStock = $variant->stock;
        $email = 'ayse-'.Str::ulid().'@example.com';

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id)
            ->assertSet('cartError', '');

        $component = Livewire::test(CheckoutPage::class)
            ->set('firstName', 'Ayşe')
            ->set('lastName', 'Yılmaz')
            ->set('email', $email)
            ->set('phone', '+90 555 111 22 33')
            ->set('lineOne', 'Test Mahallesi 1. Sokak No 2')
            ->set('city', 'İstanbul')
            ->set('postcode', '34000')
            ->call('placeOrder');

        $order = Order::query()->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertNotNull($order->reference);
        $this->assertNotNull($order->placed_at);
        $this->assertSame('payment-offline', $order->status);

        $shippingAddress = $order->shippingAddress;
        $this->assertNotNull($shippingAddress);
        $this->assertSame('Ayşe', $shippingAddress->first_name);
        $this->assertSame('Yılmaz', $shippingAddress->last_name);
        $this->assertSame('İstanbul', $shippingAddress->city);
        $this->assertSame($email, $shippingAddress->contact_email);

        $variant->refresh();
        $this->assertSame($initialStock - 1, $variant->stock);

        $this->assertNull(CartSession::current());

        $component->assertSet('orderReference', $order->reference);

        Mail::assertSent(OrderConfirmation::class);
    }

    public function test_authenticated_checkout_links_order_to_customer(): void
    {
        Mail::fake();

        $product = $this->publishedProductWithVariant(stock: 5);
        $user = $this->makeUserWithCustomer();

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id);

        $this->actingAs($user);

        Livewire::test(CheckoutPage::class)
            ->set('firstName', 'Ayşe')
            ->set('lastName', 'Yılmaz')
            ->set('email', $user->email)
            ->set('phone', '+90 555 111 22 33')
            ->set('lineOne', 'Test Mahallesi 1. Sokak No 2')
            ->set('city', 'İstanbul')
            ->set('postcode', '34000')
            ->call('placeOrder');

        $order = Order::query()->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertSame($this->testCustomer->id, $order->customer_id);
    }

    public function test_guest_checkout_creates_order_without_customer_link(): void
    {
        Mail::fake();

        $product = $this->publishedProductWithVariant(stock: 3);

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id);

        Livewire::test(CheckoutPage::class)
            ->set('firstName', 'Mehmet')
            ->set('lastName', 'Demir')
            ->set('email', 'mehmet-'.Str::ulid().'@example.com')
            ->set('phone', '+90 555 444 55 66')
            ->set('lineOne', 'Misafir Caddesi 10')
            ->set('city', 'Ankara')
            ->set('postcode', '06000')
            ->call('placeOrder');

        $order = Order::query()->latest('id')->first();

        $this->assertNotNull($order);
        $this->assertNull($order->customer_id);
    }

    public function test_checkout_validates_required_fields(): void
    {
        $component = Livewire::test(CheckoutPage::class)
            ->call('placeOrder')
            ->assertHasErrors(['firstName', 'lastName', 'email', 'phone', 'lineOne', 'city', 'postcode']);

        $component->set('email', 'gecersiz-e-posta')
            ->call('placeOrder')
            ->assertHasErrors(['email']);
    }

    public function test_checkout_rejects_empty_cart(): void
    {
        Livewire::test(CheckoutPage::class)
            ->set('firstName', 'Ayşe')
            ->set('lastName', 'Yılmaz')
            ->set('email', 'bos-sepet-'.Str::ulid().'@example.com')
            ->set('phone', '+90 555 111 22 33')
            ->set('lineOne', 'Test Mahallesi 1. Sokak No 2')
            ->set('city', 'İstanbul')
            ->set('postcode', '34000')
            ->call('placeOrder')
            ->assertHasErrors(['paymentMethod']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_stripe_payment_is_rejected_when_not_configured(): void
    {
        Mail::fake();

        $product = $this->publishedProductWithVariant(stock: 5);

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id);

        Livewire::test(CheckoutPage::class)
            ->set('firstName', 'Ayşe')
            ->set('lastName', 'Yılmaz')
            ->set('email', 'stripe-'.Str::ulid().'@example.com')
            ->set('phone', '+90 555 111 22 33')
            ->set('lineOne', 'Test Mahallesi 1. Sokak No 2')
            ->set('city', 'İstanbul')
            ->set('postcode', '34000')
            ->set('paymentMethod', 'stripe')
            ->call('placeOrder')
            ->assertHasErrors(['paymentMethod']);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_failed_payment_authorization_shows_error_and_keeps_stock(): void
    {
        Mail::fake();

        $product = $this->publishedProductWithVariant(stock: 5);
        $variant = $product->variants->first();
        $initialStock = $variant->stock;

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id);

        $fakeDriver = new class
        {
            public function cart($cart)
            {
                return $this;
            }

            public function withData($data)
            {
                return $this;
            }

            public function authorize(): PaymentAuthorize
            {
                return new PaymentAuthorize(success: false, message: 'Ödeme reddedildi');
            }
        };

        Payments::shouldReceive('driver')
            ->with('cash-in-hand')
            ->andReturn($fakeDriver);

        Livewire::test(CheckoutPage::class)
            ->set('firstName', 'Ayşe')
            ->set('lastName', 'Yılmaz')
            ->set('email', 'hata-'.Str::ulid().'@example.com')
            ->set('phone', '+90 555 111 22 33')
            ->set('lineOne', 'Test Mahallesi 1. Sokak No 2')
            ->set('city', 'İstanbul')
            ->set('postcode', '34000')
            ->call('placeOrder')
            ->assertHasErrors(['paymentMethod']);

        $this->assertSame(0, Order::query()->count());

        $variant->refresh();
        $this->assertSame($initialStock, $variant->stock);

        Mail::assertNotSent(OrderConfirmation::class);
    }

    public function test_insufficient_stock_surfaces_validation_error(): void
    {
        Mail::fake();

        $product = $this->publishedProductWithVariant(stock: 5);
        $variant = $product->variants->first();

        Livewire::test(HomePage::class)
            ->call('addToCart', $product->id);

        // Sepet oluşturulduktan sonra stok tükenmiş olsun: düşüm başarısız olmalı,
        // kullanıcıya 500 yerine doğrulama hatası gösterilmeli.
        $variant->update(['stock' => 0]);

        Livewire::test(CheckoutPage::class)
            ->set('firstName', 'Ayşe')
            ->set('lastName', 'Yılmaz')
            ->set('email', 'stok-'.Str::ulid().'@example.com')
            ->set('phone', '+90 555 111 22 33')
            ->set('lineOne', 'Test Mahallesi 1. Sokak No 2')
            ->set('city', 'İstanbul')
            ->set('postcode', '34000')
            ->call('placeOrder')
            ->assertHasErrors(['paymentMethod']);

        $variant->refresh();
        $this->assertSame(0, $variant->stock);

        $order = Order::query()->latest('id')->first();
        $this->assertNotNull($order);
        $this->assertNull(data_get($order->meta, 'stock_decremented_at'));

        Mail::assertNotSent(OrderConfirmation::class);
    }

    private function makeUserWithCustomer(): User
    {
        $user = User::factory()->create([
            'name' => 'Test Müşteri',
            'email' => 'checkout-'.Str::ulid().'@example.com',
        ]);

        $customerGroup = CustomerGroup::query()->where('default', true)->first();

        $customer = Customer::create([
            'title' => null,
            'first_name' => 'Test',
            'last_name' => 'Müşteri',
            'account_ref' => 'CHK-'.Str::ulid(),
            'attribute_data' => collect(['name' => new Text('Test Müşteri')]),
        ]);
        if ($customerGroup) {
            $customer->customerGroups()->attach($customerGroup->id);
        }
        $customer->users()->attach($user->id);

        $this->testCustomer = $customer;

        return $user;
    }

    private function publishedProductWithVariant(int $stock = 5, string $status = 'published', ?int $languageId = null): Product
    {
        TaxZone::query()->where('default', true)->exists() || TaxZone::factory()->create();

        $languageId ??= Language::factory()->create(['code' => 'tr-'.Str::ulid(), 'default' => true])->id;

        Currency::query()->where('default', true)->update(['default' => false]);
        $currency = Currency::factory()->create([
            'code' => 'TRY'.Str::ulid(),
            'default' => true,
        ]);

        $product = Product::factory()->create(['status' => $status]);

        $channel = Channel::query()->firstOrCreate(
            ['handle' => 'web'],
            ['name' => 'Web', 'url' => config('app.url'), 'default' => true],
        );
        $channel->update(['default' => true]);

        CustomerGroup::query()->where('default', true)->update(['default' => false]);
        $customerGroup = CustomerGroup::query()->firstOrCreate(
            ['handle' => 'default'],
            ['name' => 'Standard', 'default' => true],
        );
        $customerGroup->update(['default' => true]);

        $product->channels()->syncWithoutDetaching([$channel->id => ['enabled' => true]]);
        $product->customerGroups()->syncWithoutDetaching([
            $customerGroup->id => ['enabled' => true, 'purchasable' => true],
        ]);
        $product->urls()->create([
            'language_id' => $languageId,
            'slug' => 'checkout-urun-'.Str::ulid(),
            'default' => true,
        ]);

        $variant = $product->variants()->create([
            'tax_class_id' => TaxClass::factory()->create()->id,
            'sku' => 'CHECKOUT-'.Str::ulid(),
            'stock' => $stock,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'in_stock',
        ]);
        $variant->prices()->create([
            'currency_id' => $currency->id,
            'price' => 100,
            'min_quantity' => 1,
        ]);

        return $product->fresh(['variants.prices.currency', 'variants.values', 'urls', 'channels']);
    }
}