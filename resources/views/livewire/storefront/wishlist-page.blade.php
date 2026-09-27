<div>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="anim-fade-up flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 mb-3 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Alışverişe devam et
                </a>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">İstek Listem</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Beğendiğiniz ürünleri burada saklayın.</p>
            </div>
            @if (count($items) > 0)
                <span class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-rose-600 shadow-md ring-1 ring-rose-200 dark:text-rose-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    {{ count($items) }} ürün
                </span>
            @endif
        </div>

        @if (! $isAuthenticated)
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-4 text-sm text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-300">
            <span>Bu liste şu an yalnızca bu tarayıcıda saklanıyor.</span>
            <a href="{{ url('/giris') }}" class="font-semibold underline hover:text-indigo-950 dark:text-indigo-300">Giriş yap</a>
        </div>
        @endif

        @if (empty($items))
        <x-empty-state icon="❤️" title="İstek listeniz boş" description="Beğendiğiniz ürünlerdeki kalp simgesine tıklayarak listeye ekleyin.">
            <x-button href="{{ url('/') }}">Ürünleri keşfet</x-button>
        </x-empty-state>
        @else

        {{-- Sepet bildirisi ızgaranın DIŞINDA: kart içinde gösterilirse o satır
           yükseklik kalkanı ile tüm kartları uzatır ve ızgarada boşluk
           oluşturur (ana sayfa + koleksiyon sayfaları da bu deseni kullanır). --}}
        @if ($addedProductId !== null)
        {{-- wire:key sayaçla: aynı ürüne tekrar basıldığında Livewire bu düğümü
             yeniden kurar, böylece bildirim her seferinde yeniden işlenir. --}}
        <div role="status" wire:key="wishlist-cart-alert-{{ $addedCount }}"
             class="mb-6 flex flex-wrap items-center gap-x-3 gap-y-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Ürün sepete eklendi.</span>
            <a href="{{ url('/sepet') }}" class="font-semibold underline">Sepete git</a>
        </div>
        @endif
        <div class="stagger-in grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
            @foreach ($items as $item)
            <div wire:key="wishlist-item-{{ $item['id'] }}" class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
                <a href="{{ $item['url'] ? url('/urun/' . $item['url']) : '#' }}" class="flex flex-col flex-1">
                <div class="relative mx-1.5 mt-1.5 aspect-square overflow-hidden rounded-xl bg-white dark:bg-slate-900">
                    @if ($item['image_url'])
                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" loading="lazy" decoding="async" width="800" height="800" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="flex h-full w-full items-center justify-center text-6xl text-slate-300 dark:text-slate-600" aria-hidden="true">📦</div>
                    @endif
                    @if ($item['stock'] !== null && $item['stock'] <= 0)
                    <span class="absolute top-2 right-2 rounded-full bg-white px-2 py-1 text-xs font-semibold text-rose-700 shadow-md ring-1 ring-rose-200 dark:text-rose-700">Tükendi</span>
                    @endif
                </div>
                <div class="p-3 pb-0 flex-1 flex flex-col">
                    <h3 class="text-sm font-semibold text-slate-900 mb-1 line-clamp-2 group-hover:text-indigo-600 transition-colors dark:text-white dark:group-hover:text-indigo-400">{{ $item['name'] }}</h3>

                    {{-- Değerlendirme özeti (etiket rozetlerinin hemen üstünde) --}}
                    @php
                        $wishlistRatingCount = (int) ($item['reviews_count'] ?? 0);
                        $wishlistRatingValue = isset($item['rating']) && $item['rating'] !== null ? (float) $item['rating'] : null;
                    @endphp
                    @if ($wishlistRatingCount > 0 && $wishlistRatingValue !== null)
                    <div class="mb-1 flex flex-wrap items-center gap-1" data-testid="wishlist-rating" aria-label="Değerlendirme: {{ number_format($wishlistRatingValue, 1, '.', '') }} / 5, {{ $wishlistRatingCount }} değerlendirme">
                        <span class="flex items-center" aria-hidden="true">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-3.5 w-3.5 {{ $i <= (int) round($wishlistRatingValue) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                            @endfor
                        </span>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ number_format($wishlistRatingValue, 1, '.', '') }}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">({{ $wishlistRatingCount }})</span>
                    </div>
                    @endif

                    {{-- Etiket rozetleri (ürün adının altında, minimalist küçük harf) --}}
                    @php
                        $tagBadges = collect($item['tags'] ?? [])->filter()->take(3)->values();
                    @endphp
                    @if ($tagBadges->isNotEmpty())
                    <div class="mb-1.5 flex flex-wrap gap-1" data-testid="wishlist-tags">
                        @foreach ($tagBadges as $tag)
                        <span class="rounded bg-white px-1.5 py-0.5 text-[10px] font-medium lowercase tracking-wide text-slate-600 shadow-md ring-1 ring-slate-200 dark:text-slate-700">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
                </a>

                {{-- Listeden çıkar: görselin sol üst köşesi (product-card kalp deseni) --}}
                <button type="button"
                        wire:click="remove({{ $item['id'] }})"
                        wire:loading.attr="disabled"
                        wire:key="wishlist-remove-{{ $item['id'] }}"
                        title="Listeden çıkar"
                        aria-label="Listeden çıkar: {{ $item['name'] }}"
                        class="absolute top-2 left-2 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-500 shadow-sm backdrop-blur-sm transition-colors hover:text-rose-500 dark:border-slate-700 dark:bg-slate-900/90 dark:text-slate-400 dark:hover:text-rose-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3m-4 0h14"/></svg>
                </button>

                {{-- Stok etiketi: x-product-card ile aynı `match(true)` mantığı (bkz. product-card.blade.php) --}}
                @php
                    $wishlistStock = $item['stock'] ?? null;
                    $wishlistSoldOut = $wishlistStock !== null && $wishlistStock <= 0;
                    $wishlistStockLabel = match (true) {
                        $wishlistStock === null => 'Stok bilgisi yok',
                        $wishlistSoldOut => 'Stokta yok',
                        default => 'Stokta',
                    };
                @endphp
                <div class="flex flex-wrap items-end justify-between gap-x-2 gap-y-2 border-t border-slate-100 p-3 pt-2.5 dark:border-slate-800">
                    <div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $item['price'] !== null ? number_format($item['price'] / 100, 2, ',', '.') . ' ₺' : '—' }}</span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $wishlistStockLabel }}</span>
                    </div>
                    <button type="button"
                            wire:click="addToCart({{ $item['id'] }})"
                            wire:loading.attr="disabled"
                            @if ($item['stock'] === null || $item['stock'] <= 0) disabled @endif
                            title="{{ $wishlistSoldOut ? 'Stokta yok' : 'Sepete Ekle' }}"
                            aria-label="{{ $wishlistSoldOut ? 'Stokta yok' : 'Sepete Ekle' }}: {{ $item['name'] }}"
                            class="inline-flex h-9 w-9 shrink-0 select-none items-center justify-center rounded-lg bg-indigo-600 text-white shadow-sm transition-all duration-150 hover:bg-indigo-700 hover:shadow-md active:bg-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:active:bg-indigo-300 dark:focus-visible:ring-indigo-400 dark:focus-visible:ring-offset-slate-950">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </section>
</div>
