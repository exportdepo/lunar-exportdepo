<?php

namespace Tests\Feature;

use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderConfirmationMailTest extends TestCase
{
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
        $this->assertStringContainsString('Lunar Shop', $html);
        $this->assertStringContainsString('Siparişiniz alındı', $html);
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
}
