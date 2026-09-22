<?php

namespace App\Livewire\Storefront;

use App\Support\VariantOptions;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Facades\CartSession;
use Lunar\Models\ProductVariant;

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

    /**
     * Sepet satırı adedini günceller.
     *
     * PDP ile aynı stok kuralı burada da uygulanır: stok takibi olan varyantta
     * adet stoğu aşamaz. (Lunar'ın `purchasable = always` kuralı stok sınırı
     * uygulamadığı için bu kontrol uygulama tarafında yapılır.) Lunar
     * doğrulayıcılarının ürettiği `CartException` da 500'e çevrilmek yerine
     * kullanıcıya Türkçe uyarı olarak gösterilir; stepper sunucudaki gerçek
     * adete geri döndürülür.
     */
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

        $line = $cart->lines->firstWhere('id', $lineId);

        if (! $line) {
            // Satır başka bir sekmede kaldırılmış olabilir.
            $this->addError('cart', 'Bu ürün artık sepetinizde bulunmuyor.');
            $this->refreshCart();
            $this->dispatch('cart-updated');

            return;
        }

        $stock = $line->purchasable instanceof ProductVariant ? $line->purchasable->stock : null;
        $rejected = $stock !== null && $quantity > $stock;

        if ($rejected) {
            $this->addError('cart', $this->stockMessage($stock));
        } else {
            try {
                $cart->updateLine($lineId, $quantity);
                $this->resetErrorBag('cart');
            } catch (CartException $exception) {
                $rejected = true;
                $this->addError('cart', $this->cartErrorMessage($exception));
            }
        }

        $this->refreshCart();

        if ($rejected) {
            // Alpine stepper'ı sunucudaki adete eşitler (adet reddedildiğinde
            // istemci tarafında kalan hatalı değeri temizler).
            $this->dispatch('cart-quantity-rejected', lineId: $lineId, quantity: $this->lineQuantity($lineId));
        }

        $this->dispatch('cart-updated');
    }

    public function removeLine(int $lineId): void
    {
        $cart = CartSession::current();
        if (! $cart) {
            return;
        }

        try {
            $cart->remove($lineId);
            $this->resetErrorBag('cart');
        } catch (CartException) {
            $this->addError('cart', 'Ürün sepetten kaldırılamadı.');
        }

        $this->refreshCart();
        $this->dispatch('cart-updated');
    }

    /** Stok üstü adet için kullanıcıya gösterilecek mesaj. */
    protected function stockMessage(int $stock): string
    {
        return $stock > 0
            ? "Stokta en fazla {$stock} adet bulunuyor."
            : 'Bu ürün şu anda stokta yok.';
    }

    /**
     * Lunar doğrulayıcı mesajları İngilizce üretilir (ör. `CartLineStock`);
     * vitrinde gösterilecek Türkçe metni burada oluşturuyoruz.
     */
    protected function cartErrorMessage(CartException $exception): string
    {
        return $exception->errors()->has('purchasable')
            ? 'Bu ürün şu anda satın alınamıyor.'
            : 'Seçilen adet stok veya satış kurallarına uygun değil.';
    }

    /** Sepetteki satırın sunucudaki güncel adedi (satır yoksa 1). */
    protected function lineQuantity(int $lineId): int
    {
        return (int) (collect($this->lines)->firstWhere('id', $lineId)['quantity'] ?? 1);
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

        $this->lines = $cart->lines->load(['purchasable.values.option', 'purchasable.product.media'])->map(function ($line): array {
            $variant = $line->purchasable;
            $product = $variant?->product;
            $image = $product?->media->first();

            return [
                'id' => $line->id,
                'name' => (string) ($product?->translateAttribute('name') ?? 'Ürün'),
                'options' => VariantOptions::label($variant),
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
        return view('livewire.storefront.cart-page')->layoutData([
            'title' => 'Sepetim',
        ]);
    }
}
