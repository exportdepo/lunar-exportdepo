<div>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 mb-3 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Alışverişe devam et
                </a>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">İstek Listem</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Beğendiğiniz ürünleri burada saklayın.</p>
            </div>
            @if (count($items) > 0)
                <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600 dark:bg-rose-900/20 dark:text-rose-400">
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
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $item)
            <div wire:key="wishlist-item-{{ $item['id'] }}" class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition-all hover:shadow-xl dark:border-slate-800 dark:bg-slate-950">
                <a href="{{ $item['url'] ? url('/urun/' . $item['url']) : '#' }}" class="flex flex-col flex-1">
                <div class="relative aspect-square overflow-hidden bg-slate-100 dark:bg-slate-800">
                    @if ($item['image_url'])
                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" loading="lazy" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="flex h-full w-full items-center justify-center text-6xl text-slate-300 dark:text-slate-600">📦</div>
                    @endif
                    @if ($item['stock'] !== null && $item['stock'] <= 0)
                    <span class="absolute top-2 right-2 rounded-full px-2 py-1 text-xs font-semibold bg-rose-100 text-rose-800">Tükendi</span>
                    @endif
                </div>
                <div class="p-4 pb-0 flex-1 flex flex-col">
                    <h3 class="font-semibold text-slate-900 mb-1 line-clamp-2 group-hover:text-indigo-600 transition-colors dark:text-white dark:group-hover:text-indigo-400">{{ $item['name'] }}</h3>
                    <p class="text-sm text-slate-500 mb-3 line-clamp-2 flex-1 dark:text-slate-400">{{ $item['description'] }}</p>
                </div>
                </a>
                <div class="flex flex-wrap items-end justify-between gap-x-3 gap-y-2 border-t border-slate-100 p-4 pt-3 dark:border-slate-800">
                    <div>
                        <span class="text-lg font-bold text-slate-900 dark:text-white">{{ $item['price'] !== null ? number_format($item['price'] / 100, 2, ',', '.') . ' ₺' : '—' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($item['stock'] !== null && $item['stock'] > 0)
                        <x-button wire:click="addToCart({{ $item['id'] }})" wire:loading.attr="disabled" variant="primary" size="sm" type="button" class="shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Sepete Ekle
                        </x-button>
                        @else
                        <span class="inline-flex shrink-0 items-center justify-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">Stok yok</span>
                        @endif
                        <x-button wire:click="remove({{ $item['id'] }})" wire:loading.attr="disabled" variant="ghost" size="sm" type="button" title="Listeden çıkar" aria-label="Listeden çıkar" class="shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0 1 16.138 21H7.862a2 2 0 0 1-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v3m-4 0h14"/></svg>
                        </x-button>
                    </div>
                </div>
                @if ($addedProductId === $item['id'])
                <div class="px-4 pb-4 -mt-1">
                <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-800">Ürün sepete eklendi. <a href="{{ url('/sepet') }}" class="font-semibold underline">Sepete git</a></p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </section>
</div>