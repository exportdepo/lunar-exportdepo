<div>
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 text-center">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-4">
                ExportDepo'ya Hoş Geldiniz
            </h1>
            <p class="text-lg sm:text-xl text-indigo-100 max-w-2xl mx-auto mb-8">
                Türkiye'nin en yeni e-ticaret deneyimi. Kaliteli ürünler, uygun fiyatlar, hızlı teslimat.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="#urunler" class="inline-flex items-center px-6 py-3 rounded-lg bg-white text-indigo-700 font-semibold shadow-lg hover:bg-slate-100 transition-colors">
                    Alışverişe Başla
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
                <a href="{{ url('/sepet') }}" class="inline-flex items-center px-6 py-3 rounded-lg bg-white/10 backdrop-blur text-white font-semibold border border-white/20 hover:bg-white/20 transition-colors">
                    Sepete Git
                </a>
            </div>
        </div>
    </section>

    {{-- Koleksiyon Kartları --}}
    @if (!empty($collections))
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Kategoriler</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach ($collections as $collection)
                    <a href="{{ url('/koleksiyon/' . ($collection['url'] ?? $collection['id'])) }}"
                       class="group relative overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-shadow aspect-[4/3]">
                        @if ($collection['image_url'])
                            <img src="{{ $collection['image_url'] }}"
                                 alt="{{ $collection['name'] }}"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-5 text-white">
                            <h3 class="text-xl font-bold mb-1">{{ $collection['name'] }}</h3>
                            <p class="text-sm text-white/80 line-clamp-1">{{ $collection['description'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
    {{-- Ürün Listesi --}}
    <section id="urunler" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-slate-900">@if (filled($q)) «{{ $q }}» için arama sonuçları @else Tüm Ürünler @endif</h2>
            <span class="text-sm text-slate-500">{{ count($products) }} {{ filled($q) ? 'sonuç' : 'ürün' }}</span>
        </div>

        @if (empty($products))
            <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-12 text-center">
                <div class="text-5xl mb-4">📦</div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">@if (filled($q)) Aramanızla eşleşen ürün bulunamadı @else Henüz ürün yok @endif</h3>
                <p class="text-slate-500">@if (filled($q)) Farklı anahtar kelimelerle tekrar deneyin. @else Demo veri yüklenmedi. @endif</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <div class="relative">
                    <a href="{{ $product['url'] ? url('/urun/' . $product['url']) : '#' }}"
                       class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all overflow-hidden border border-slate-100 flex flex-col">
                        <div class="relative aspect-square overflow-hidden bg-slate-100">
                            @if ($product['image_url'])
                                <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300 text-6xl">📦</div>
                            @endif
                            @if ($product['stock'] !== null && $product['stock'] <= 5 && $product['stock'] > 0)
                                <span class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Son {{ $product['stock'] }}</span>
                            @elseif ($product['stock'] !== null && $product['stock'] <= 0)
                                <span class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Tükendi</span>
                            @endif
                        </div>
                        <div class="p-4 flex-1 flex flex-col">
                            <h3 class="font-semibold text-slate-900 mb-1 line-clamp-2 group-hover:text-indigo-600 transition-colors">
                                {{ $product['name'] }}
                            </h3>
                            <p class="text-sm text-slate-500 mb-3 line-clamp-2 flex-1">
                                {{ $product['description'] }}
                            </p>
                            <div class="flex items-end justify-between mt-auto pt-2">
                                <div>
                                    <div class="text-lg font-bold text-slate-900">
                                        {{ $product['price'] !== null ? number_format($product['price'] / 100, 2, ',', '.') . ' ₺' : '—' }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        @if ($product['stock'] !== null && $product['stock'] > 0) Stokta @else Stokta yok @endif
                                    </div>
                                </div>
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                    <button type="button" wire:click="toggle({{ $product['id'] }})" wire:key="wishlist-{{ $product['id'] }}"
                            title="{{ $this->inWishlist($product['id']) ? 'İstek listesinden çıkar' : 'İstek listesine ekle' }}"
                            aria-label="{{ $this->inWishlist($product['id']) ? 'İstek listesinden çıkar' : 'İstek listesine ekle' }}"
                            class="absolute top-2 left-2 z-10 inline-flex h-9 w-9 items-center justify-center rounded-full border shadow-sm backdrop-blur transition-colors
                                   {{ $this->inWishlist($product['id']) ? 'border-rose-200 bg-white text-rose-500' : 'border-slate-200 bg-white/90 text-slate-500 hover:text-rose-500' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="{{ $this->inWishlist($product['id']) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>