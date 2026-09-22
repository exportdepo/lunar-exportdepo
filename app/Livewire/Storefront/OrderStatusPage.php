<?php

namespace App\Livewire\Storefront;

use App\Support\VariantOptions;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Lunar\Models\Order;

#[Layout('layouts.storefront')]
class OrderStatusPage extends Component
{
    public string $reference = '';

    /**
     * Sipariş yalnızca referansla yüklenir; istemci snapshot'ında
     * değiştirilememesi için kilitlidir.
     */
    #[Locked]
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
                'options' => VariantOptions::label($line->purchasable),
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

    /**
     * Sipariş meta'sı `AsArrayObject` cast'i ile gelir (ArrayObject), bu yüzden
     * `is_array()` yerine Arrayable kontrolü gerekir. Stripe ödemeleri
     * `StripePayment::authorize()` tarafından `stripe_session_id` yazılarak
     * işaretlenir (offline ödemelerde `payment_method` meta'sı bulunur).
     */
    public function paymentMethod(): string
    {
        $meta = $this->order?->meta;
        $meta = $meta instanceof Arrayable ? $meta->toArray() : (array) $meta;

        if (($meta['payment_method'] ?? null) === 'stripe' || isset($meta['stripe_session_id'])) {
            return 'Kart ile ödeme (Stripe)';
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
