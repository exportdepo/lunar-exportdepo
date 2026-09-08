<?php

namespace App\Http\Controllers;

use App\Services\OrderConfirmationService;
use App\Services\OrderStockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Lunar\Models\Order;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $secret = config('services.stripe.webhook_secret');

        abort_unless($secret, 503, 'Stripe webhook yapılandırılmamış.');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret,
            );
        } catch (\Throwable) {
            return response('Invalid webhook signature.', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $orderId = $session->metadata->order_id ?? null;
            $order = $orderId ? Order::find($orderId) : null;
            $expectedSessionId = $order ? data_get($order->meta, 'stripe_session_id') : null;

            if (
                $order
                && $order->isDraft()
                && $session->payment_status === 'paid'
                && $expectedSessionId === $session->id
            ) {
                $order->update([
                    'status' => 'payment-received',
                    'placed_at' => now(),
                ]);
                app(OrderStockService::class)->decrementFor($order->fresh('productLines'));
                app(OrderConfirmationService::class)->send($order->fresh());
            }
        }

        return response('Webhook received.', 200);
    }
}
