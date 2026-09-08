<div>
    <section class="bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20">
            <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-slate-300 hover:text-white mb-6 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Tüm ürünler
            </a>
            <div class="grid gap-8 md:grid-cols-[minmax(0,1fr)_20rem] md:items-center">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-indigo-300 mb-3">Koleksiyon</p>
                    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight">{{ $collection['name'] }}</h1>
                    <p class="mt-4 max-w-2xl text-lg text-slate-300">{{ $collection['description'] }}</p>
                </div>
                @if ($collection['image_url'])
                    <img src="{{ $collection['image_url'] }}" alt="{{ $collection['name'] }}" class="w-full aspect-[4/3] rounded-2xl object-cover shadow-2xl">
                @endif
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <h2 class="text-2xl font-bold text-slate-900">{{ $collection['name'] }} ürünleri</h2>
            
            <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
                <div class="relative w-full sm:w-64">
                    <input wire:model.live.debounce.300ms="searchTerm" type="text" placeholder="Ürün ara..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                @if (!empty($categories))
                    <div class="w-full sm:w-48">
                        <select wire:model="selectedCategoryId" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Tüm Kategoriler</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (!empty($variantAttributes))
                    <div class="w-full sm:w-48">
                        <select wire:model="selectedVariantAttributeId" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Tüm Özellikler</option>
                            @foreach ($variantAttributes as $attribute)
                                <option value="{{ $attribute['attribute_id'] }}">{{ $attribute['attribute_name'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-48">
                        <select wire:model="selectedVariantValue" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" {{ $selectedVariantAttributeId ? '' : 'disabled' }}>
                            <option value="">Değer Seçin</option>
                            @foreach ($variantAttributes as $attribute)
                                @if ($attribute['attribute_id'] == $selectedVariantAttributeId)
                                    @foreach ($attribute['values'] as $value)
                                        <option value="{{ $value['value'] }}">{{ $value['label'] }}</option>
                                    @endforeach
                                @endif
                            @endforeach
                        </select>
                    </div>
                @endif

                @if ($searchTerm || $selectedCategoryId || $selectedVariantValue)
                    <button wire:click="clearFilters" class="px-4 py-2 text-sm text-indigo-600 hover:text-indigo-800 underline transition-colors">
                        Filtreleri Temizle
                    </button>
                @endif

                <span class="text-sm text-slate-500">{{ count($products) }} ürün</span>
            </div>
        </div>

        @if (empty($products))
            <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-12 text-center">
                <h3 class="text-xl font-semibold text-slate-800 mb-2">Bu koleksiyonda ürün yok</h3>
                <p class="text-slate-500">Yakında yeni ürünler eklenecek.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($products as $product)
                    <a href="{{ $product['url'] ? url('/urun/' . $product['url']) : '#' }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all overflow-hidden border border-slate-100 flex flex-col">
                        <div class="relative aspect-square overflow-hidden bg-slate-100">
                            @if ($product['image_url'])
                                <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
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
                            <h3 class="font-semibold text-slate-900 mb-1 line-clamp-2 group-hover:text-indigo-600 transition-colors">{{ $product['name'] }}</h3>
                            <p class="text-sm text-slate-500 mb-3 line-clamp-2 flex-1">{{ $product['description'] }}</p>
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
                @endforeach
            </div>
        @endif
    </section>
</div>
