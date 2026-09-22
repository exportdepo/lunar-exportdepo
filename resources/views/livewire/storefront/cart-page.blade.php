{{--
    Sepet sayfası (Phase 5a).

    Akış: satır listesi (görsel, ad, birim fiyat, adet stepper, satır toplamı,
    kaldır) + sağ sticky sipariş özeti (ara toplam, KDV, kargo, toplam, CTA).
    Adetleme: PDP ile aynı Alpine stepper desen. `updateQuantity` ve
    `removeLine` Livewire aksiyonları korunur.
--}}
<div>
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-12 lg:px-8">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">ExportDepo</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Sepetim</h1>
            </div>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-indigo-600 transition-colors hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Alışverişe devam et</a>
        </div>

        @if (empty($lines))
            <x-empty-state icon="🛒"
                           title="Sepetiniz boş"
                           description="Beğendiğiniz ürünleri sepete ekleyerek başlayabilirsiniz.">
                <x-button href="{{ url('/') }}" size="sm">Ürünleri keşfet</x-button>
            </x-empty-state>
        @else
            <div class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
                {{-- Satırlar --}}
                <ul class="space-y-4">
                    @foreach ($lines as $line)
                        <li class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="cart-line-{{ $line['id'] }}">
                            <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                                @if ($line['image_url'])
                                    <img src="{{ $line['image_url'] }}"
                                         alt="{{ $line['name'] }}"
                                         loading="lazy"
                                         decoding="async"
                                         width="200"
                                         height="200"
                                         class="h-full w-full object-cover">
                                @else
                                    <span class="flex h-full w-full items-center justify-center text-4xl text-slate-300 dark:text-slate-600" aria-hidden="true">📦</span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <h2 class="truncate font-semibold text-slate-900 dark:text-white">{{ $line['name'] }}</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ number_format($line['unit_price'] / 100, 2, ',', '.') }} ₺ / adet
                                    <span class="text-xs text-slate-400">(KDV dahil)</span>
                                </p>

                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <div x-data="{ qty: {{ (int) $line['quantity'] }} }" class="flex items-stretch overflow-hidden rounded-lg border border-slate-300 transition-colors focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/30 dark:border-slate-600 dark:focus-within:border-indigo-400 dark:focus-within:ring-indigo-400/30">
                                        <button type="button"
                                                @click="qty = Math.max(1, (parseInt(qty) || 1) - 1); $wire.updateQuantity({{ $line['id'] }}, qty)"
                                                :disabled="(parseInt(qty) || 1) <= 1"
                                                aria-label="{{ $line['name'] }} adet azalt"
                                                class="px-2.5 text-slate-600 transition-colors hover:bg-slate-100 disabled:opacity-40 dark:text-slate-300 dark:hover:bg-slate-800">−</button>
                                        <input type="number"
                                               min="1"
                                               :value="qty"
                                               @change="$wire.updateQuantity({{ $line['id'] }}, $event.target.value)"
                                               aria-label="{{ $line['name'] }} adet"
                                               class="qty-input w-14 py-1.5 text-center text-sm font-semibold text-slate-900 dark:bg-slate-800 dark:text-slate-100" />
                                        <button type="button"
                                                @click="qty = Math.min(99, (parseInt(qty) || 1) + 1); $wire.updateQuantity({{ $line['id'] }}, qty)"
                                                aria-label="{{ $line['name'] }} adet artır"
                                                class="px-2.5 text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">+</button>
                                    </div>

                                    <button type="button"
                                            wire:click="removeLine({{ $line['id'] }})"
                                            title="Ürünü kaldır"
                                            aria-label="{{ $line['name'] }} ürününü kaldır"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-500 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="shrink-0 text-right font-bold text-slate-900 dark:text-white">
                                {{ number_format($line['total'] / 100, 2, ',', '.') }} ₺
                            </div>
                        </li>
                    @endforeach
                </ul>

                {{-- Sticky özet --}}
                <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:sticky lg:top-24">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Sipariş özeti</h2>

                    <div class="mt-5 space-y-2 px-1 py-3 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-400">Ürünler (KDV hariç)</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($subTotal / 100, 2, ',', '.') }} ₺</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-400">KDV</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($taxTotal / 100, 2, ',', '.') }} ₺</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-400">Kargo</span>
                            <span class="font-semibold text-slate-900 dark:text-white">{{ $shippingTotal > 0 ? number_format($shippingTotal / 100, 2, ',', '.') . ' ₺' : 'Ücretsiz' }}</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3 dark:border-slate-700">
                            <span class="font-semibold text-slate-700 dark:text-slate-200">Toplam</span>
                            <span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format($total / 100, 2, ',', '.') }} ₺</span>
                        </div>
                    </div>

                    <x-button block href="{{ url('/checkout') }}" class="mt-6">Ödemeye geç</x-button>
                </aside>
            </div>
        @endif
    </section>
</div>
