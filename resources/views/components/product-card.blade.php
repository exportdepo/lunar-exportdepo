{{--
    ExportDepo atomik ürün kartı bileşeni.

    Kullanım:
        <x-product-card :product="$product" />
        <x-product-card :product="$product" :add-to-cart="true" />
        <x-product-card :product="$product" :show-wishlist="true" :in-wishlist="$this->inWishlist($product['id'])" />

    Beklenen `$product` dizisi (mevcut Livewire sayfalarıyla uyumlu):
        ['id', 'name', 'description', 'price' (kuruş), 'stock', 'image_url', 'url' (slug)]

    Notlar:
    - `addToCart = true` iken sepet ikonlu "Sepete Ekle" düğmesi (ikon-only;
      `title`/`aria-label` ile etiketli, tükendiğinde `disabled`) üst Livewire
      bileşenindeki `addToCart($id)` aksiyonunu çağırır.
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
    <a href="{{ $href }}" class="relative block overflow-hidden" aria-label="{{ $name }}">
        {{-- Görsel kart kenarına yapışmasın: üstte ve yanda 6px nefes payı, altta yok.
             Köşeler kartın rounded-2xl'ine uyumlu olsun diye rounded-xl. --}}
        <div class="mx-1.5 mt-1.5 aspect-square overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
            @if (filled($image))
            <img src="{{ $image }}"
                 alt="{{ $name }}"
                 loading="lazy"
                 decoding="async"
                 width="800"
                 height="800"
                 class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
            @else
            <span class="flex h-full w-full items-center justify-center text-5xl text-slate-300 dark:text-slate-600" aria-hidden="true">📦</span>
            @endif

        </div>
        @if ($soldOut)
            <span class="absolute top-2 right-2 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-rose-700 shadow-md ring-1 ring-rose-200 dark:text-rose-700">Tükendi</span>
        @elseif ($lowStock)
            <span class="absolute top-2 right-2 rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-amber-700 shadow-md ring-1 ring-amber-200 dark:text-amber-700">Son {{ $stock }}</span>
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

    {{-- Kart içeriği — yanal boşluk istek listesi kartıyla aynı ölçüde (p-4) tutulur --}}
    <div class="flex flex-1 flex-col p-3">
        <a href="{{ $href }}" class="mb-1">
            <h3 class="line-clamp-2 text-sm font-semibold text-slate-900 transition-colors group-hover:text-indigo-600 dark:text-slate-100 dark:group-hover:text-indigo-400">
                {{ $name }}
            </h3>
        </a>

        {{-- Değerlendirme özeti (etiket rozetlerinin hemen üstünde) --}}
        @php
            $ratingCount = (int) ($product['reviews_count'] ?? 0);
            $ratingValue = isset($product['rating']) && $product['rating'] !== null ? (float) $product['rating'] : null;
        @endphp
        @if ($ratingCount > 0 && $ratingValue !== null)
            <div class="mb-1 flex flex-wrap items-center gap-1" data-testid="card-rating" aria-label="Değerlendirme: {{ number_format($ratingValue, 1, '.', '') }} / 5, {{ $ratingCount }} değerlendirme">
                <span class="flex items-center" aria-hidden="true">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="h-3.5 w-3.5 {{ $i <= (int) round($ratingValue) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                    @endfor
                </span>
                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ number_format($ratingValue, 1, '.', '') }}</span>
                <span class="text-xs text-slate-500 dark:text-slate-400">({{ $ratingCount }})</span>
            </div>
        @endif

        {{-- Etiket rozetleri (ürün adının altında, minimalist küçük harf) --}}
        @php
            $tagBadges = collect($product['tags'] ?? [])->filter()->take(3)->values();
        @endphp
        @if ($tagBadges->isNotEmpty())
            <div class="mb-1.5 flex flex-wrap gap-1" data-testid="card-tags">
                @foreach ($tagBadges as $tag)
                    <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-medium lowercase tracking-wide text-slate-600 shadow-md ring-1 ring-slate-200 dark:text-slate-700">{{ $tag }}</span>
                @endforeach
            </div>
        @endif

        <div class="mt-auto flex flex-wrap items-end justify-between gap-x-2 gap-y-2 border-t border-slate-100 pt-2.5 dark:border-slate-800">
            <div>
                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $price ?? '—' }}</span>
                <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $stockLabel }}</span>
            </div>

            @if ($addToCart && $productId)
                <button type="button"
                        wire:click="addToCart({{ $productId }})"
                        wire:loading.attr="disabled"
                        @if ($soldOut) disabled @endif
                        title="{{ $soldOut ? 'Stokta yok' : 'Sepete Ekle' }}"
                        aria-label="{{ $soldOut ? 'Stokta yok' : 'Sepete Ekle' }}: {{ $name }}"
                        class="inline-flex h-9 w-9 shrink-0 select-none items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm transition-all duration-150 hover:bg-indigo-700 hover:shadow-md active:bg-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:active:bg-indigo-300 dark:focus-visible:ring-indigo-400 dark:focus-visible:ring-offset-slate-950">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </button>
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
