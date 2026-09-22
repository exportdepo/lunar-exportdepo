<?php

namespace Tests\Feature;

use App\Livewire\Storefront\OrderStatusPage;
use Livewire\Livewire;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Models\Channel;
use Lunar\Models\Order;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class OrderStatusPageTest extends TestCase
{
    use SeedsLunarDefaults;

    protected ?Order $testOrder = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    protected function tearDown(): void
    {
        if ($this->testOrder) {
            $this->testOrder->delete();
            $this->testOrder = null;
        }
        parent::tearDown();
    }

    protected function createOrder(array $overrides = []): Order
    {
        $channel = Channel::first() ?? Channel::factory()->create();

        $this->testOrder = Order::create(array_merge([
            'channel_id' => $channel->id,
            'new_customer' => true,
            'status' => 'payment-offline',
            'reference' => 'TESTREF'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT),
            'sub_total' => 10000,
            'discount_total' => 0,
            'shipping_total' => 0,
            'tax_breakdown' => new TaxBreakdown,
            'tax_total' => 2000,
            'total' => 12000,
            'currency_code' => 'TRY',
            'compare_currency_code' => 'TRY',
            'exchange_rate' => 1,
            'placed_at' => now(),
        ], $overrides));

        return $this->testOrder;
    }

    public function test_order_status_page_renders_for_known_reference(): void
    {
        $order = $this->createOrder();

        $this->get('/siparis/'.$order->reference)
            ->assertOk()
            ->assertSee('#'.$order->reference)
            ->assertSee('Manuel Ödeme')
            ->assertSee('Sipariş Özeti')
            ->assertSee('Teslimat Adresi');
    }

    public function test_order_status_page_returns_404_for_unknown_reference(): void
    {
        $this->get('/siparis/DOESNOTEXIST')
            ->assertNotFound();
    }

    public function test_order_status_page_shows_turkish_status_labels(): void
    {
        $cases = [
            'awaiting-payment' => 'Ödeme Bekleniyor',
            'payment-offline' => 'Manuel Ödeme',
            'payment-received' => 'Ödeme Alındı',
            'dispatched' => 'Kargoya Verildi',
        ];

        foreach ($cases as $status => $expectedLabel) {
            $order = $this->createOrder(['status' => $status]);
            $this->get('/siparis/'.$order->reference)
                ->assertOk()
                ->assertSee($expectedLabel);
            $this->testOrder->delete();
            $this->testOrder = null;
        }
    }

    public function test_order_status_component_returns_localized_data(): void
    {
        $order = $this->createOrder();

        $component = Livewire::test(OrderStatusPage::class, ['reference' => $order->reference]);

        $component->assertSet('reference', $order->reference);

        $instance = $component->instance();
        $this->assertSame('Manuel Ödeme', $instance->statusLabel());
        $this->assertSame('#0ea5e9', $instance->statusColor());
        $this->assertSame('bg-sky-500', $instance->statusBadgeClass());
        $this->assertSame('Manuel ödeme', $instance->paymentMethod());
        $this->assertCount(0, $instance->lines());
    }

    /**
     * Ödeme yöntemi `order.meta` üzerinden okunur; bu alan `AsArrayObject`
     * cast'i ile geldiği için ArrayObject/`stripe_session_id` kombinasyonları
     * Stripe etiketiyle gösterilmelidir.
     */
    public function test_order_status_page_labels_stripe_payments_from_order_meta(): void
    {
        $cases = [
            ['status' => 'awaiting-payment', 'meta' => ['payment_method' => 'stripe']],
            ['status' => 'awaiting-payment', 'meta' => ['stripe_session_id' => 'cs_test_123']],
        ];

        foreach ($cases as $case) {
            $order = $this->createOrder($case);

            $this->get('/siparis/'.$order->reference)
                ->assertOk()
                ->assertSee('Kart ile ödeme (Stripe)');

            $this->testOrder->delete();
            $this->testOrder = null;
        }
    }

    /**
     * `order` kilidi (Locked) ve typed model property'nin Livewire
     * hydration turunda sorun çıkarmadığını doğrular.
     */
    public function test_order_status_component_survives_a_hydration_round_trip(): void
    {
        $order = $this->createOrder(['meta' => ['stripe_session_id' => 'cs_test_456']]);

        Livewire::test(OrderStatusPage::class, ['reference' => $order->reference])
            ->call('$refresh')
            ->assertSee('Kart ile ödeme (Stripe)')
            ->assertSet('reference', $order->reference);
    }
}
