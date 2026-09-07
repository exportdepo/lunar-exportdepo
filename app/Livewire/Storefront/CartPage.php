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
        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    protected function refreshCart(): void
    {
        $cart = CartSession::current();

        if (! $cart) {
            $this->lines = [];
            $this->total = 0;
            return;
        }

        $this->lines = $cart->lines->map(function ($line): array {
            $variant = $line->purchasable;
            $product = $variant?->product;
            $image = $product?->media->first();

            return [
                'id' => $line->id,
                'name' => (string) ($product?->translateAttribute('name') ?? 'Ürün'),
                'image_url' => $image?->getUrl(),
                'quantity' => (int) $line->quantity,
                'unit_price' => $line->unitPrice?->value ?? 0,
                'total' => $line->total?->value ?? 0,
            ];
        })->all();

        $this->total = $cart->total?->value ?? 0;
    }

    public function render()
    {
        return view('livewire.storefront.cart-page');
    }
}
