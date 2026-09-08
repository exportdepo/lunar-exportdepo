<?php

namespace Tests\Feature;

use App\Http\Controllers\StripeWebhookController;
use App\Services\OrderStockService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Process;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Tests\TestCase;

class StripeIntegrationTest extends TestCase
{
    public function test_stripe_webhook_can_process_payment_successfully(): void
    {
        Mail::fake();
        
        // Create a test order
        $order = Order::factory()->create([
            'status' => 'payment-received',
        ]);
        
        // Create a product variant with stock
        $variant = $this->createStockedVariant(10);
        
        // Create an order line for this variant
        $line = OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'quantity' => 1,
        ]);
        
        $initialStock = $variant->stock;
        
        // Mock Stripe webhook payload
        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_123456789',
                    'status' => 'succeeded',
                    'amount' => 10000,
                    'currency' => 'try',
                    'description' => 'Test Order',
                    'customer' => 'cus_123456789',
                    'metadata' => [
                        'order_reference' => $order->reference,
                    ],
                ],
            ],
        ];
        
        // Call webhook controller - this will process the webhook
        $response = $this->postJson('/stripe/webhook', $payload);
        
        // Assert webhook returned success
        $response->assertStatus(200);
        
        // Verify stock was decremented
        $variant->refresh();
        $this->assertSame($initialStock - 1, $variant->stock);
        
        // Verify order is marked as paid
        $order->refresh();
        $this->assertSame('payment-received', $order->status);
        
        // Verify order confirmation email was sent
        Mail::assertSent(\App\Mail\OrderConfirmation::class);
    }
    
    public function test_order_stock_is_rolled_back_on_insufficient_stock(): void
    {
        Mail::fake();
        
        // Create order with insufficient stock
        $variant = $this->createStockedVariant(1);  // Only 1 in stock
        
        $order = Order::factory()->create([
            'status' => 'payment-received',
        ]);
        
        // Create an order line for this variant with quantity 2 (more than stock)
        $line = OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'quantity' => 2,  // More than available stock
        ]);
        
        $initialStock = $variant->stock;
        
        // Mock Stripe webhook payload
        $payload = [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_123456789',
                    'status' => 'succeeded',
                    'amount' => 10000,
                    'currency' => 'try',
                    'description' => 'Test Order',
                    'customer' => 'cus_123456789',
                    'metadata' => [
                        'order_reference' => $order->reference,
                    ],
                ],
            ],
        ];
        
        // The webhook call should throw an exception due to insufficient stock
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Sipariş için yeterli ürün stoğu bulunmuyor.');
        
        // Call webhook controller
        $this->postJson('/stripe/webhook', $payload);
        
        // Verify stock was NOT decremented
        $variant->refresh();
        $this->assertSame($initialStock, $variant->stock);
        
        // Verify order is still in processing state (not paid)
        $order->refresh();
        $this->assertNull(data_get($order->meta, 'stock_decremented_at'));
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