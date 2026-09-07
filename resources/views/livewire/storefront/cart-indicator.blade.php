<div class="relative" id="header-cart">
    <button type="button" id="cart-toggle" aria-expanded="false" aria-controls="cart-dropdown" class="relative inline-flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-indigo-600 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <span class="hidden sm:inline">Sepet</span>
        <span id="cart-badge" class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-5 h-5 px-1 text-xs font-bold text-white bg-indigo-600 rounded-full">{{ $count }}</span>
    </button>
    <div id="cart-dropdown" hidden class="absolute right-0 mt-3 w-80 max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200 bg-white p-4 shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="font-bold text-slate-900">Sepetim</h2>
            <span class="text-xs text-slate-500">{{ $count }} ürün</span>
        </div>
        @if (empty($lines))
            <p class="py-8 text-center text-sm text-slate-500">Sepetiniz boş.</p>
        @else
            <div class="max-h-72 space-y-3 overflow-y-auto py-3">
                @foreach ($lines as $line)
                    <div class="flex items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-800">{{ $line['name'] }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-600">
                                {{ $line['quantity'] }} adet
                            </p>
                        </div>
                        <span class="text-sm font-semibold text-slate-900">{{ number_format($line['total'] / 100, 2, ',', '.') }} ₺</span>
                        <button type="button" wire:click="removeLine({{ $line['id'] }})" title="Ürünü kaldır" aria-label="{{ $line['name'] }} ürününü kaldır" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m-4 0h14" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
            <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                <span class="font-semibold text-slate-700">Toplam</span>
                <span class="font-bold text-slate-900">{{ number_format($total / 100, 2, ',', '.') }} ₺</span>
            </div>
            <div class="mt-4 grid grid-cols-2 gap-2">
                <a href="{{ url('/sepet') }}" class="rounded-lg border border-slate-300 px-3 py-2 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Sepeti gör</a>
                <a href="{{ url('/checkout') }}" class="rounded-lg bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700">Ödeme</a>
            </div>
        @endif
    </div>
    <script>
        (() => {
            const toggle = document.getElementById('cart-toggle');
            const dropdown = document.getElementById('cart-dropdown');
            const container = document.getElementById('header-cart');
            if (!toggle || !dropdown || !container || toggle.dataset.ready) return;
            toggle.dataset.ready = 'true';
            toggle.addEventListener('click', () => {
                const isOpen = !dropdown.hidden;
                dropdown.hidden = isOpen;
                toggle.setAttribute('aria-expanded', String(!isOpen));
            });
            document.addEventListener('click', (event) => {
                if (!container.contains(event.target)) {
                    dropdown.hidden = true;
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        })();
    </script>
</div>
