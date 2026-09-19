{{--
    ExportDepo atomik ürün kartı bileşeni.

    Kullanım:
        <x-product-card :product="$product" />
        <x-product-card :product="$product" :add-to-cart="true" />
        <x-product-card :product="$product" :show-wishlist="true" :in-wishlist="$this->inWishlist($product['id'])" />

    Beklenen `$product` dizisi (mevcut Livewire sayfalarıyla uyumlu):
        ['id', 'name', 'description', 'price' (kuruş), 'stock', 'image_url', 'url' (slug)]

    Notlar:
    - `addToCart = true` iken "Sepete Ekle" düğmesi üst Livewire bileşenindeki
      `addToCart($id)` aksiyonunu çağırır (Phase 4 sayfa entegrasyonunda tanımlanır).
    - Kart, grid içinde eşit yükseklikte durması için `h-full` + flex kolon yapısındadır.
--}}
@props([
    'product',
    'addToCart' => true,
    'showWishlist' => false,
    'inWishlist' => null,
])

@php
    $name = $product['name'] ?? '';
    $description = $product['description'] ?? '';
    $productId = $product['id'] ?? null;
    $slug = $product['url'] ?? ($product['slug'] ?? null);
    $href = filled($slug) ? url('/urun/'.$slug) : ($productId ? url('/urun/'.$productId) : '#');

    $price = null;
    if (isset($product['price']) && $product['price'] !== null) {
        $price = is_numeric($product['price'])
            ? number_format($product['price'] / 100, 2, ',', '.').' ₺'
            : (string) $product['price'];
    }

    $stock = $product['stock'] ?? null;
    $image = $product['image_url'] ?? null;
    $soldOut = $stock !== null && $stock <= 0;
    $lowStock = ! $soldOut && $stock !== null && $stock <= 5;

    $stockLabel = match (true) {
        $stock === null => 'Stok bilgisi yok',
        $soldOut => 'Stokta yok',
        default => 'Stokta',
    };
@endphp

<article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
    {{-- Ürün görseli: hover'da hafifçe büyür --}}
    <a href="{{ $href }}" class="relative block aspect-square overflow-hidden bg-slate-100 dark:bg-slate-800" aria-label="{{ $name }}">
        @if (filled($image))
            <img src="{{ $image }}"
                 alt="{{ $name }}"
                 loading="lazy"
                 class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
        @else
            <span class="flex h-full w-full items-center justify-center text-5xl text-slate-300 dark:text-slate-600" aria-hidden="true">📦</span>
        @endif

        @if ($soldOut)
            <span class="absolute top-2 right-2 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-500/20 dark:text-rose-300">Tükendi</span>
        @elseif ($lowStock)
            <span class="absolute top-2 right-2 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-500/20 dark:text-amber-300">Son {{ $stock }}</span>
        @endif
    </a>

    {{-- İstek listesi düğmesi (opsiyonel) --}}
    @if ($showWishlist && $productId)
        <button type="button"
                wire:click="toggle({{ $productId }})"
                wire:key="wishlist-{{ $productId }}"
                title="{{ $inWishlist ? 'İstek listesinden çıkar' : 'İstek listesine ekle' }}"
                aria-label="{{ $inWishlist ? 'İstek listesinden çıkar' : 'İstek listesine ekle' }}"
                class="absolute top-2 left-2 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border shadow-sm backdrop-blur-sm transition-colors
                       {{ $inWishlist
                           ? 'border-rose-200 bg-white text-rose-500 dark:border-rose-500/40 dark:bg-slate-900/90 dark:text-rose-400'
                           : 'border-slate-200 bg-white/90 text-slate-500 hover:text-rose-500 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-400 dark:hover:text-rose-400' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>
    @endif

    {{-- Kart içeriği --}}
    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <a href="{{ $href }}" class="mb-1">
            <h3 class="line-clamp-2 font-semibold text-slate-900 transition-colors group-hover:text-indigo-600 dark:text-slate-100 dark:group-hover:text-indigo-400">
                {{ $name }}
            </h3>
        </a>

        <p class="mb-4 line-clamp-2 flex-1 text-sm text-slate-500 dark:text-slate-400">
            {{ $description }}
        </p>

        <div class="mt-auto flex items-end justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
            <div>
                <span class="text-lg font-bold text-slate-900 dark:text-white">{{ $price ?? '—' }}</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $stockLabel }}</span>
            </div>

            @if ($addToCart && $productId)
                <x-button size="sm"
                          wire:click="addToCart({{ $productId }})"
                          :disabled="$soldOut"
                          wire:loading.attr="disabled"
                          class="shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Sepete Ekle
                </x-button>
            @else
                <a href="{{ $href }}"
                   aria-label="{{ $name }} ürününü incele"
                   class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 transition-colors hover:bg-indigo-600 hover:text-white dark:bg-indigo-500/15 dark:text-indigo-400 dark:hover:bg-indigo-500 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
</article>
