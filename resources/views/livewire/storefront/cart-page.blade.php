<div>
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-end justify-between mb-8">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">ExportDepo</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-slate-900">Sepetim</h1>
            </div>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Alışverişe devam et</a>
        </div>

        @if (empty($lines))
            <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-14 text-center">
                <div class="text-5xl mb-4">🛒</div>
                <h2 class="text-xl font-semibold text-slate-800">Sepetiniz boş</h2>
                <p class="mt-2 text-slate-500">Beğendiğiniz ürünleri sepete ekleyerek başlayabilirsiniz.</p>
                <a href="{{ url('/') }}" class="mt-6 inline-flex rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Ürünleri keşfet</a>
            </div>
        @else
            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                <div class="space-y-4">
                    @foreach ($lines as $line)
                        <div class="flex gap-4 p-4">
                            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-slate-100">
                                @if ($line['image_url'])
                                    <img src="{{ $line['image_url'] }}" alt="{{ $line['name'] }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="font-semibold text-slate-900">{{ $line['name'] }}</h2>
                                <p class="mt-1 text-sm text-slate-500">{{ number_format($line['unit_price'] / 100, 2, ',', '.') }} ₺ / adet</p>
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600">
                                        <span>Adet</span>
                                        <input type="number" min="1" wire:change="updateQuantity({{ $line['id'] }}, $event.target.value)" value="{{ $line['quantity'] }}" aria-label="{{ $line['name'] }} adet" class="w-14 px-2 py-1 text-center text-sm font-semibold text-slate-900">
                                    </label>
                                    <button type="button" wire:click="removeLine({{ $line['id'] }})" title="Ürünü kaldır" aria-label="{{ $line['name'] }} ürününü kaldır" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="shrink-0 px-3 py-2 text-right font-bold text-slate-900">
                                {{ number_format($line['total'] / 100, 2, ',', '.') }} ₺
                            </div>
                        </div>
                    @endforeach
                </div>

                <aside class="p-6">
                    <h2 class="text-lg font-bold text-slate-900">Sipariş özeti</h2>
                    <div class="mt-5 flex items-center justify-between px-4 py-3">
                        <span class="font-semibold text-slate-700">Toplam</span>
                        <span class="text-2xl font-extrabold text-slate-900">{{ number_format($total / 100, 2, ',', '.') }} ₺</span>
                    </div>
                    <a href="{{ url('/checkout') }}" class="mt-6 inline-flex w-full justify-center rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Ödemeye geç</a>
                </aside>
            </div>
        @endif
    </section>
</div>
