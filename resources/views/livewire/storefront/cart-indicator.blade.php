{{--
    ExportDepo sepet rozeti + cekmece icerigi (Livewire).

    - Dugme, Alpine `$store.cartDrawer.toggle()` ile cekmeceyi acar/kapatir.
    - Cekmece kabugu `components/cart-drawer.blade.php` bilesenidir; sepet
      satirlari slot'a, toplamlar ve CTA'lar `footer` slot'una aktarilir.
    - Sepet verisi `CartSession` uzerinden gelir; `cart-updated` olayinda
      yenilenir (urun ekleme/cikarma islemleri bu olayi tetikler).
--}}
<div class="shrink-0">
    <button type="button"
            @click="$store.cartDrawer.toggle()"
            aria-label="Sepeti aç"
            class="relative inline-flex items-center gap-2 rounded-lg px-3 py-2 text-slate-600 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="hidden xl:inline">Sepet</span>
        <span id="cart-badge" class="absolute -right-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1 text-xs font-bold text-white">{{ $count }}</span>
        @if ($count > 0)
            <span class="hidden text-sm font-bold text-slate-900 dark:text-white xl:inline">{{ number_format($total / 100, 2, ',', '.') }} ₺</span>
        @endif
    </button>

    <x-cart-drawer :title="'Sepetim'.($count > 0 ? ' ('.$count.' ürün)' : '')">
        @if (empty($lines))
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <span class="text-5xl" aria-hidden="true">🛒</span>
                <h3 class="mt-4 text-base font-semibold text-slate-900 dark:text-white">Sepetiniz şu an boş</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Alışverişe başlamak için ürünleri keşfedin.</p>
                <x-button href="{{ url('/') }}" size="sm" class="mt-5">Alışverişe Başla</x-button>
            </div>
        @else
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach ($lines as $line)
                    <li class="flex items-center gap-3 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $line['name'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $line['quantity'] }} adet</p>
                        </div>
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ number_format($line['total'] / 100, 2, ',', '.') }} ₺</span>
                        <button type="button"
                                wire:click="removeLine({{ $line['id'] }})"
                                wire:key="cart-remove-{{ $line['id'] }}"
                                title="Ürünü kaldır"
                                aria-label="{{ $line['name'] }} ürününü kaldır"
                                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-500 dark:hover:bg-rose-500/10 dark:hover:text-rose-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                            </svg>
                        </button>
                    </li>
                @endforeach
            </ul>

            <x-slot:footer>
                <div class="space-y-1 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 dark:text-slate-400">Ara toplam (KDV hariç)</span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($subTotal / 100, 2, ',', '.') }} ₺</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-600 dark:text-slate-400">KDV</span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ number_format($taxTotal / 100, 2, ',', '.') }} ₺</span>
                    </div>
                    <div class="mt-2 flex items-center justify-between border-t border-slate-100 pt-2 dark:border-slate-800">
                        <span class="font-semibold text-slate-700 dark:text-slate-200">Toplam</span>
                        <span class="text-base font-bold text-slate-900 dark:text-white">{{ number_format($total / 100, 2, ',', '.') }} ₺</span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <x-button variant="outline" size="sm" href="{{ url('/sepet') }}">Sepeti Gör</x-button>
                    <x-button size="sm" href="{{ url('/checkout') }}">Ödemeye Geç</x-button>
                </div>
            </x-slot:footer>
        @endif
    </x-cart-drawer>
</div>
