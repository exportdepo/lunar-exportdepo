<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use App\Services\OrderStockService;
use Illuminate\Support\Facades\Mail;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\OrderAddress;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class OrderConfirmationMailTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    public function test_mailable_uses_correct_subject_and_view(): void
    {
        $order = new \Lunar\Models\Order(['reference' => 'ORD-TEST-1234']);
        $order->reference = 'ORD-TEST-1234';

        $mailable = new OrderConfirmation($order);

        $envelope = $mailable->envelope();
        $this->assertStringContainsString('ORD-TEST-1234', $envelope->subject);
        $this->assertStringContainsString('Siparişiniz alındı', $envelope->subject);

        $content = $mailable->content();
        $this->assertSame('emails.order-confirmation', $content->view);
    }

    public function test_mailable_html_contains_order_reference_and_branding(): void
    {
        $order = new \Lunar\Models\Order(['reference' => 'ORD-DEMO-9999']);
        $order->reference = 'ORD-DEMO-9999';

        $mailable = new OrderConfirmation($order);

        $html = $mailable->render();

        $this->assertStringContainsString('ORD-DEMO-9999', $html);
        $this->assertStringContainsString('ExportDepo', $html);
        $this->assertStringNotContainsString('Lunar Shop', $html);
        $this->assertStringContainsString('Siparişiniz alındı', $html);
    }

    public function test_mailable_html_supports_dark_mode_scheme(): void
    {
        $order = new \Lunar\Models\Order(['reference' => 'ORD-DARK-1']);
        $order->reference = 'ORD-DARK-1';

        $html = (new OrderConfirmation($order))->render();

        $this->assertStringContainsString('name="color-scheme"', $html);
        $this->assertStringContainsString('prefers-color-scheme: dark', $html);
        $this->assertStringContainsString('email-card', $html);
    }

    public function test_mail_facade_can_be_faked_for_checkout_flow(): void
    {
        // Bu test sadece Mail::fake()'in test ortamında çalıştığını doğrular.
        // Asıl sipariş akışı e2e testi için integration test eklenebilir.
        Mail::fake();

        $fakeMailable = new OrderConfirmation(
            new \Lunar\Models\Order(['reference' => 'ORD-FAKE-1'])
        );
        $fakeMailable->reference = 'ORD-FAKE-1';

        Mail::assertNothingSent();
        Mail::send($fakeMailable);
        Mail::assertSent(OrderConfirmation::class);
    }

    public function test_order_confirmation_service_sends_once_to_billing_email(): void
    {
        Mail::fake();
        $order = Order::factory()->create();
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

        $service = app(\App\Services\OrderConfirmationService::class);
        $service->send($order->fresh());
        $service->send($order->fresh());

        Mail::assertSent(OrderConfirmation::class, function (OrderConfirmation $mail): bool {
            return $mail->order->reference !== null;
        });
        Mail::assertSent(OrderConfirmation::class, 1);
        $this->assertNotNull(data_get($order->fresh()->meta, 'order_confirmation_sent_at'));

        $order->billingAddress->delete();
        $order->delete();
    }

    public function test_order_stock_is_decremented_once_for_variant_lines(): void
    {
        $variant = $this->createStockedVariant(5);

        $order = Order::factory()->create();
        $line = OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'quantity' => 2,
        ]);
        $initialStock = $variant->stock;

        $service = app(OrderStockService::class);
        $service->decrementFor($order->fresh('productLines'));
        $service->decrementFor($order->fresh('productLines'));

        $this->assertSame($initialStock - $line->quantity, $variant->fresh()->stock);
        $this->assertNotNull(data_get($order->fresh()->meta, 'stock_decremented_at'));

        $line->delete();
        $order->delete();
        $variant->product->delete();
    }

    public function test_order_stock_rolls_back_when_quantity_exceeds_stock(): void
    {
        $variant = $this->createStockedVariant(1);
        $order = Order::factory()->create();
        $line = OrderLine::factory()->create([
            'order_id' => $order->id,
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'quantity' => 2,
        ]);

        try {
            app(OrderStockService::class)->decrementFor($order);
            $this->fail('Yetersiz stokta exception bekleniyordu.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('yeterli', $exception->getMessage());

            $this->assertSame(1, $variant->fresh()->stock);
            $this->assertNull(data_get($order->fresh()->meta, 'stock_decremented_at'));
        } finally {
            $line->delete();
            $order->delete();
            $variant->product->delete();
        }
    }

    private function createStockedVariant(int $stock): ProductVariant
    {
        Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        $product = Product::factory()->create(['status' => 'published']);

        return $product->variants()->create([
            'tax_class_id' => TaxClass::factory()->create()->id,
            'sku' => 'TEST-STOCK-'.uniqid(),
            'stock' => $stock,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'in_stock',
        ]);
    }
}
