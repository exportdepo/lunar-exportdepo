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

    /**
     * Sepet ürün sayısı değiştiğinde ekran okuyucuya duyurulacak metin.
     *
     * `null` → henüz duyuru yok. Bölge ilk yüklemede boş başlar; yalnızca
     * gerçek bir değişiklikte doldurulur (WCAG 4.1.3 — gereksiz bildirim
     * yapılmaz).
     */
    public ?string $cartAnnouncement = null;

    public function mount(): void
    {
        $this->refreshCart();

        // İlk yüklemede duyuru yapılmaz: sepetteki ürün sayısı bir
        // değişiklik değildir, yalnızca mevcut durumu bildirir.
        $this->cartAnnouncement = null;
    }

    #[On('cart-updated')]
    public function refreshCart(): void
    {
        $previousCount = $this->count;

        $cart = CartSession::current();

        if (! $cart) {
            $this->lines = [];
            $this->count = 0;
            $this->total = 0;
            $this->subTotal = 0;
            $this->taxTotal = 0;

            $this->announceCountChange($previousCount);

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

        $this->announceCountChange($previousCount);
    }

    /**
     * Ürün sayısı değiştiyse canlı bölgedeki metni günceller.
     *
     * Sayı değişmediyse metin olduğu gibi bırakılır: aynı metin yeniden
     * yazılırsa ekran okuyucu gereksiz yere tekrar duyurur.
     */
    protected function announceCountChange(int $previousCount): void
    {
        if ($this->count !== $previousCount) {
            $this->cartAnnouncement = "Sepetinizde {$this->count} ürün var.";
        }
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
