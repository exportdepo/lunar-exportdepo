<?php

namespace Tests\Feature;

use App\Http\Controllers\StripeWebhookController;
use App\Mail\OrderConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class StripeIntegrationTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    public function test_stripe_webhook_can_process_payment_successfully(): void
    {
        Mail::fake();
        $secret = 'whsec_test_webhook_secret';
        config(['services.stripe.webhook_secret' => $secret]);

        $variant = $this->createStockedVariant(10);
        $order = Order::factory()->create(['status' => 'draft']);
        $order->update(['meta' => ['stripe_session_id' => 'cs_test_session_123']]);
        OrderAddress::create([
            'order_id' => $order->id,
            'type' => 'billing',
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'line_one' => 'Test Street 1',
            'city' => 'Istanbul',
            'postcode' => '34000',
            'contact_email' => 'customer@example.com',
        ]);
        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'quantity' => 1,
        ]);
        $initialStock = $variant->stock;
        $payload = json_encode([
            'id' => 'evt_test_123',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_session_123',
                    'payment_status' => 'paid',
                    'customer' => 'cus_test',
                    'metadata' => ['order_id' => $order->id],
                ],
            ],
        ]);

        $response = $this->dispatchWebhook($payload, $secret);
        $response->assertStatus(200);

        $variant->refresh();
        $this->assertSame($initialStock - 1, $variant->stock);
        $order->refresh();
        $this->assertSame('payment-received', $order->status);
        Mail::assertSent(OrderConfirmation::class);
    }

    public function test_order_stock_is_rolled_back_on_insufficient_stock(): void
    {
        Mail::fake();
        $secret = 'whsec_test_webhook_secret';
        config(['services.stripe.webhook_secret' => $secret]);

        $variant = $this->createStockedVariant(1);
        $order = Order::factory()->create(['status' => 'draft']);
        $order->update(['meta' => ['stripe_session_id' => 'cs_test_session_insufficient']]);
        OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'quantity' => 2,
        ]);
        $initialStock = $variant->stock;
        $payload = json_encode([
            'id' => 'evt_test_456',
            'object' => 'event',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_session_insufficient',
                    'payment_status' => 'paid',
                    'customer' => 'cus_test',
                    'metadata' => ['order_id' => $order->id],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sipariş için yeterli ürün stoğu bulunmuyor.');

        try {
            $this->dispatchWebhook($payload, $secret);
        } finally {
            $variant->refresh();
            $this->assertSame($initialStock, $variant->stock);
        }
    }

    private function dispatchWebhook(string $payload, string $secret): TestResponse
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        $request = Request::create('/stripe/webhook', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], $payload);

        $controller = new StripeWebhookController();

        return TestResponse::fromBaseResponse($controller($request));
    }

    private function createStockedVariant(int $stock): ProductVariant
    {
        $product = \Lunar\Models\Product::factory()->create(['status' => 'published']);
        return $product->variants()->create([
            'tax_class_id' => TaxClass::factory()->create()->id,
            'sku' => 'TEST-STOCK-' . uniqid(),
            'stock' => $stock,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'in_stock',
        ]);
    }
}