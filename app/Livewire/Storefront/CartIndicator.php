<?php

namespace App\Livewire\Storefront;

use App\Support\VariantOptions;
use Livewire\Attributes\On;
use Livewire\Component;
use Lunar\Facades\CartSession;

class CartIndicator extends Component
{
    public array $lines = [];

    public int $count = 0;

    public int $total = 0;

    public int $subTotal = 0;

    public int $taxTotal = 0;

    public function mount(): void
    {
        $this->refreshCart();
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $cart = CartSession::current();

        if (! $cart) {
            $this->lines = [];
            $this->count = 0;
            $this->total = 0;
            $this->subTotal = 0;
            $this->taxTotal = 0;

            return;
        }

        $cart->refresh();
        $cart->recalculate();

        $this->lines = $cart->lines->load('purchasable.values.option')->map(function ($line): array {
            $product = $line->purchasable?->product;

            return [
                'id' => $line->id,
                'name' => (string) ($product?->translateAttribute('name') ?? 'Ürün'),
                'options' => VariantOptions::label($line->purchasable),
                'quantity' => (int) $line->quantity,
                'total' => (int) (($line->unitPriceInclTax?->value ?? ($line->unitPrice?->value ?? 0)) * $line->quantity),
            ];
        })->all();
        $this->count = array_sum(array_column($this->lines, 'quantity'));
        $this->subTotal = $cart->subTotal?->value ?? 0;
        $this->taxTotal = $cart->taxTotal?->value ?? 0;
        $this->total = $cart->total?->value ?? 0;
    }

    public function removeLine(int $lineId): void
    {
        $cart = CartSession::current();

        if (! $cart) {
            return;
        }

        $cart->remove($lineId);
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.storefront.cart-indicator');
    }
}
