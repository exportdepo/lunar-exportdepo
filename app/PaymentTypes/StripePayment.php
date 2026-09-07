<?php

namespace App\PaymentTypes;

use Lunar\Base\DataTransferObjects\PaymentAuthorize;
use Lunar\Base\DataTransferObjects\PaymentCapture;
use Lunar\Base\DataTransferObjects\PaymentRefund;
use Lunar\Models\Contracts\Transaction as TransactionContract;
use Lunar\PaymentTypes\AbstractPayment;
use Stripe\StripeClient;

class StripePayment extends AbstractPayment
{
    public function authorize(): ?PaymentAuthorize
    {
        if (! $this->cart) {
            return new PaymentAuthorize(false, 'Stripe ödeme için sepet bulunamadı.');
        }

        $order = $this->cart->draftOrder()->first() ?: $this->cart->createOrder(false);
        $client = new StripeClient($this->config['secret']);
        $lineItems = $this->cart->lines->map(function ($line): array {
            return [
                'price_data' => [
                    'currency' => strtolower($this->cart->currency->code),
                    'product_data' => ['name' => $line->purchasable->getName()],
                    'unit_amount' => $line->unitPrice->value,
                ],
                'quantity' => $line->quantity,
            ];
        })->values()->all();

        $session = $client->checkout->sessions->create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'customer_email' => $this->cart->billingAddress?->contact_email,
            'metadata' => ['order_id' => $order->id],
            'success_url' => $this->config['success_url'].'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->config['cancel_url'],
        ]);

        $order->update(['meta' => array_merge((array) $order->meta, ['stripe_session_id' => $session->id])]);

        return new PaymentAuthorize(true, $session->url, $order->id, 'stripe');
    }

    public function refund(TransactionContract $transaction, int $amount = 0, $notes = null): PaymentRefund
    {
        return new PaymentRefund(false, 'Stripe iadeleri yönetim panelinden başlatılmalıdır.');
    }

    public function capture(TransactionContract $transaction, $amount = 0): PaymentCapture
    {
        return new PaymentCapture(false, 'Stripe Checkout ödemeleri otomatik olarak tamamlanır.');
    }
}
