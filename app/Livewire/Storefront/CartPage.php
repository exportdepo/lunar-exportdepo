<?php

namespace App\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Facades\CartSession;

#[Layout('layouts.storefront')]
class CartPage extends Component
{
    public array $lines = [];

    public int $total = 0;

    public int $subTotal = 0;

    public int $taxTotal = 0;

    public int $shippingTotal = 0;

    public function mount(): void
    {
        $this->refreshCart();
    }

    public function updateQuantity(int $lineId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeLine($lineId);
            return;
        }

        $cart = CartSession::current();
        if (! $cart) {
            return;
        }

        $cart->updateLine($lineId, $quantity);
        $cart->refresh();
        $cart->recalculate();
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    public function removeLine(int $lineId): void
    {
        $cart = CartSession::current();
        if (! $cart) {
            return;
        }

        $cart->remove($lineId);
        $cart->refresh();
        $cart->recalculate();
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    protected function refreshCart(): void
    {
        $cart = CartSession::current();

        if (! $cart) {
            $this->lines = [];
            $this->total = 0;
            $this->subTotal = 0;
            $this->taxTotal = 0;
            $this->shippingTotal = 0;
            return;
        }

        $cart->refresh();
        $cart->recalculate();

        $this->lines = $cart->lines->map(function ($line): array {
            $variant = $line->purchasable;
            $product = $variant?->product;
            $image = $product?->media->first();

            return [
                'id' => $line->id,
                'name' => (string) ($product?->translateAttribute('name') ?? 'Ürün'),
                'image_url' => $image?->getUrl(),
                'quantity' => (int) $line->quantity,
                'unit_price' => $line->unitPriceInclTax?->value ?? ($line->unitPrice?->value ?? 0),
                'total' => (int) (($line->unitPriceInclTax?->value ?? ($line->unitPrice?->value ?? 0)) * $line->quantity),
            ];
        })->all();

        $this->subTotal = $cart->subTotal?->value ?? 0;
        $this->taxTotal = $cart->taxTotal?->value ?? 0;
        $this->shippingTotal = $cart->shippingTotal?->value ?? 0;
        $this->total = $cart->total?->value ?? 0;
    }

    public function render()
    {
        return view('livewire.storefront.cart-page');
    }
}
