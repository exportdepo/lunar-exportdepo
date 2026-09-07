<div>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 mb-8 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Alışverişe devam et
        </a>

        <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
            <div class="aspect-square overflow-hidden rounded-3xl bg-slate-100 shadow-sm">
                @if ($product['image_url'])
                    <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-300 text-8xl">📦</div>
                @endif
            </div>

            <div class="pt-2">
                @if ($product['collection'] && $product['collection_url'])
                    <a href="{{ url('/koleksiyon/' . $product['collection_url']) }}" class="text-sm font-semibold uppercase tracking-widest text-indigo-600 hover:text-indigo-800">
                        {{ $product['collection'] }}
                    </a>
                @endif
                <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900">{{ $product['name'] }}</h1>
                <div class="mt-5 text-3xl font-bold text-slate-900">
                    {{ $product['price'] !== null ? number_format($product['price'] / 100, 2, ',', '.') . ' ₺' : '—' }}
                </div>
                <p class="mt-6 text-lg leading-8 text-slate-600">{{ $product['description'] }}</p>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    @if ($product['stock'] !== null && $product['stock'] > 0)
                        <div class="mb-4 text-sm font-medium text-emerald-700">Stokta: {{ $product['stock'] }} adet</div>
                        <form wire:submit="addToCart" class="flex flex-wrap items-end gap-3">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-slate-700 mb-2">Adet</label>
                                <input id="quantity" type="number" min="1" max="{{ $product['stock'] }}" wire:model="quantity" class="w-24 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                Sepete Ekle
                            </button>
                        </form>
                        @error('quantity') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        @if ($added)
                            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                Ürün sepete eklendi. <a href="{{ url('/sepet') }}" class="font-semibold underline">Sepete git</a>
                            </div>
                        @endif
                    @else
                        <p class="rounded-lg bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">Bu ürün şu anda stokta yok.</p>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
