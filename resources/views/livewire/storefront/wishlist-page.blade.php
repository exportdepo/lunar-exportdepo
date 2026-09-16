<div>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 mb-3 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Alışverişe devam et
                </a>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">İstek Listem</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Beğendiğiniz ürünleri burada saklayın, dilediğiniz zaman sepete ekleyin.
                </p>
            </div>
            @if (count($items) > 0)
                <span class="inline-flex items-center gap-2 rounded-full bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    {{ count($items) }} ürün
                </span>
            @endif
        </div>

        @if (! $isAuthenticated)
            <div class="mb-8 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-indigo-100 bg-indigo-50 px-5 py-4 text-sm text-indigo-800">
                <span>Bu liste şu an yalnızca bu tarayıcıda saklanıyor. Giriş yaparsanız hesabınıza kaydedilir.</span>
                <a href="{{ url('/giris') }}" class="font-semibold underline hover:text-indigo-950">Giriş yap</a>
            </div>
        @endif

        @if (empty($items))
            <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-12 text-center">
                <div class="text-5xl mb-4">🤍</div>
                <h2 class="text-xl font-semibold text-slate-800 mb-2">İstek listeniz boş</h2>
                <p class="text-slate-500 mb-6">Beğendiğiniz ürünlerdeki kalp simgesine dokunarak listeye ekleyebilirsiniz.</p>
                <a href="{{ url('/') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white hover:bg-indigo-700 transition-colors">
                    Ürünlere göz at
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($items as $item)
                    <div class="group relative bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all overflow-hidden border border-slate-100 flex flex-col">
                        <a href="{{ $item['url'] ? url('/urun/' . $item['url']) : '#' }}" class="flex flex-col flex-1">
                            <div class="relative aspect-square overflow-hidden bg-slate-100">
                                @if ($item['image_url'])
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-300 text-6xl">📦</div>
                                @endif
                                @if ($item['stock'] !== null && $item['stock'] <= 0)
                                    <span class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Tükendi</span>
                                @endif
                            </div>
                            <div class="p-4 flex-1 flex flex-col">
                                <h3 class="font-semibold text-slate-900 mb-1 line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ $item['name'] }}</h3>
                                <p class="text-sm text-slate-500 mb-3 line-clamp-2 flex-1">{{ $item['description'] }}</p>
                                <div class="text-lg font-bold text-slate-900">
                                    {{ $item['price'] !== null ? number_format($item['price'] / 100, 2, ',', '.') . ' ₺' : '—' }}
                                </div>
                            </div>
                        </a>
                        <div class="px-4 pb-4 flex items-center gap-2">
                            @if ($item['stock'] !== null && $item['stock'] > 0)
                                <button type="button" wire:click="addToCart({{ $item['id'] }})" class="inline-flex flex-1 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                                    Sepete Ekle
                                </button>
                            @else
                                <span class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
                                    Stokta yok
                                </span>
                            @endif
                            <button type="button" wire:click="remove({{ $item['id'] }})" title="İstek listesinden çıkar" aria-label="{{ $item['name'] }} ürününü istek listesinden çıkar" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-400 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                </svg>
                            </button>
                        </div>
                        @if ($addedProductId === $item['id'])
                            <div class="px-4 pb-4 -mt-1">
                                <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-800">
                                    Ürün sepete eklendi. <a href="{{ url('/sepet') }}" class="font-semibold underline">Sepete git</a>
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>