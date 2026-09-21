<?php

namespace App\Livewire\Storefront;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Order;

#[Layout('layouts.storefront')]
class OrderStatusPage extends Component
{
    public string $reference = '';

    public ?Order $order = null;

    public function mount(string $reference): void
    {
        $this->reference = $reference;

        $order = Order::with(['productLines.purchasable.product', 'billingAddress', 'shippingAddress', 'currency'])
            ->where('reference', $reference)
            ->first();

        if (! $order) {
            throw (new ModelNotFoundException)->setModel(Order::class, [$reference]);
        }

        $this->order = $order;
    }

    public function lines(): array
    {
        if (! $this->order) {
            return [];
        }

        return $this->order->productLines->map(function ($line): array {
            $product = $line->purchasable?->product;
            $name = (string) ($product?->translateAttribute('name') ?? 'Ürün');

            return [
                'name' => $name,
                'quantity' => (int) $line->quantity,
                'unit_price' => $line->unitPrice?->value ?? 0,
                'total' => $line->total?->value ?? 0,
            ];
        })->all();
    }

    public function statusLabel(): string
    {
        return match ($this->order?->status) {
            'awaiting-payment' => 'Ödeme Bekleniyor',
            'payment-offline' => 'Manuel Ödeme',
            'payment-received' => 'Ödeme Alındı',
            'dispatched' => 'Kargoya Verildi',
            default => $this->order?->status_label ?? 'Bilinmiyor',
        };
    }

    public function statusColor(): string
    {
        return match ($this->order?->status) {
            'awaiting-payment' => '#f59e0b',
            'payment-offline' => '#0ea5e9',
            'payment-received' => '#10b981',
            'dispatched' => '#8b5cf6',
            default => '#64748b',
        };
    }

    public function paymentMethod(): string
    {
        $meta = $this->order?->meta;
        if (is_array($meta) && isset($meta['payment_method'])) {
            return $meta['payment_method'] === 'stripe' ? 'Kart ile ödeme (Stripe)' : 'Manuel ödeme';
        }

        return $this->order?->status === 'awaiting-payment' ? 'Ödeme Bekleniyor' : 'Manuel ödeme';
    }

    public function render()
    {
        return view('livewire.storefront.order-status-page')->layoutData([
            'title' => 'Sipariş #'.$this->reference,
        ]);
    }
}
