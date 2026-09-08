<?php

namespace App\Services;

use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Lunar\Models\Order;

class OrderConfirmationService
{
    public function send(Order $order): void
    {
        if (data_get($order->meta, 'order_confirmation_sent_at')) {
            return;
        }

        $order->loadMissing(['billingAddress', 'shippingAddress']);
        $email = $order->billingAddress?->contact_email
            ?? $order->shippingAddress?->contact_email;

        if (! $email) {
            Log::warning('Sipariş onay maili gönderilemedi: e-posta adresi bulunamadı.', [
                'order_id' => $order->id,
                'reference' => $order->reference,
            ]);

            return;
        }

        try {
            Mail::to($email)->send(new OrderConfirmation($order));
            $order->update([
                'meta' => array_merge((array) $order->meta, [
                    'order_confirmation_sent_at' => now()->toIso8601String(),
                ]),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Sipariş onay maili gönderilemedi.', [
                'order_id' => $order->id,
                'reference' => $order->reference,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}