<?php

namespace App\Livewire\Storefront\Concerns;

use App\Support\WishlistManager;

/**
 * Sayfa bileşenlerine istek listesi (wishlist) davranışını ekler.
 *
 * Mimari not: ürün kartlarındaki kalp ikonları AYRI Livewire alt
 * bileşenleri DEĞİLDİR; her sayfa kendi butonlarını çizip kendi
 * toggle() metodunu sunar. Böylece Livewire v3'ün nested component
 * sorunları (wire:id text leak, snapshot çakışmaları, eski önbellekli
 * sekmelerden gelen 500'ler) mimari olarak ortadan kalkar.
 *
 * toggle() imzası eski sayfa snapshot'larıyla (bfcache/önbellekli
 * sekmeler) uyumludur: hem parametresiz hem toggle(id) çağrıları
 * kabul edilir ve 500 yerine gerçek işlem yapılır.
 */
trait InteractsWithWishlist
{
    /** @var array<int, int> Aktif kullanıcının/oturumun listesindeki ürün ID'leri. */
    public array $wishlistIds = [];

    public function bootInteractsWithWishlist(): void
    {
        $this->wishlistIds = WishlistManager::ids();
    }

    /**
     * Ürünü istek listesine ekler ya da listeden çıkarır.
     */
    public function toggle(?int $productId = null): void
    {
        $id = $productId;

        // Ürün detay sayfasında parametre gelmezse sayfanın ürününü kullan.
        if (! $id && property_exists($this, 'product') && is_array($this->product)) {
            $id = $this->product['id'] ?? null;
        }

        if (! $id) {
            return;
        }

        WishlistManager::toggle((int) $id);

        // Tek doğruluk kaynağı (sunucu) üzerinden görünüm durumunu tazele.
        $this->wishlistIds = WishlistManager::ids();

        $this->dispatch('wishlist-updated');
    }

    public function inWishlist(int $productId): bool
    {
        return in_array($productId, $this->wishlistIds, true);
    }
}
