<?php

namespace App\Livewire\Storefront\Concerns;

use App\Livewire\Storefront\CartIndicator;
use Lunar\Facades\CartSession;
use Lunar\Models\Channel;
use Lunar\Models\ProductVariant;

/**
 * Ürün listelerinden (ana sayfa, koleksiyon) tek tıkla sepete ekleme.
 *
 * Ürün detay sayfasındaki (ProductPage::addToCart) akış ile aynı kuralları
 * izler: yalnızca yayında olan ürünün stokta olan ilk varyantı sepete eklenir.
 * Başarılı eklemede `cart-updated` olayı yayılır; header'daki sepet rozeti
 * (CartIndicator) yeni adet ve tutarla güncellenir. Sepet yüzeyleri (hover
 * popup kartı ve sağ çekmece) otomatik açılmaz; kullanıcı bunları hover/tıklama
 * ile kendisi açar.
 */
trait InteractsWithCart
{
    /** Sepete ekleme başarısız olduğunda gösterilecek mesaj. */
    public string $cartError = '';

    public function addToCart(int $productId): void
    {
        $this->cartError = '';

        $variant = $this->firstPurchasableVariant($productId);

        if (! $variant) {
            $this->cartError = 'Bu ürün şu anda satın alınamıyor.';

            return;
        }

        $channel = $variant->product->channels()
            ->where('handle', 'web')
            ->first();

        if ($channel instanceof Channel) {
            CartSession::setChannel($channel);
        }

        CartSession::manager()->add($variant, 1);

        $this->dispatch('cart-updated')->to(CartIndicator::class);
    }

    /**
     * Ürünün stokta olan ilk varyantını bulur. Stok takibi yapılmayan
     * (stock = null) varyantlar da satın alınabilir kabul edilir.
     */
    protected function firstPurchasableVariant(int $productId): ?ProductVariant
    {
        return ProductVariant::query()
            ->whereHas('product', fn ($query) => $query
                ->whereKey($productId)
                ->where('status', 'published'))
            ->where(fn ($query) => $query
                ->whereNull('stock')
                ->orWhere('stock', '>', 0))
            ->orderBy('id')
            ->first();
    }
}
