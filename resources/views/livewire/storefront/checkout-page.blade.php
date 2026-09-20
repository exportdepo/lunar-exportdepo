<div class="checkout-page">
    <section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
        @if ($orderReference)
            <x-cart-alert type="success" icon="✓">
                <x-slot:header>
                    <h1 class="text-3xl font-extrabold text-emerald-900 dark:text-emerald-200">Siparişiniz alındı</h1>
                </x-slot:header>
                <p>Sipariş numaranız: <strong class="text-base">{{ $orderReference }}</strong></p>
                <p class="mt-1">Ödeme yöntemi: {{ $paymentMethod === 'stripe' ? 'Kart ile ödeme' : 'Manuel ödeme' }}</p>
                <p class="mt-1">Sipariş onayını e-posta adresinize gönderdik.</p>
                <x-slot:footer>
                    <div class="flex flex-col justify-center gap-3 sm:flex-row">
                        <x-button href="{{ url('/siparis/' . $orderReference) }}" variant="secondary">Siparişinizi takip edin</x-button>
                        <x-button href="{{ url('/') }}" variant="outline">Alışverişe dön</x-button>
                    </div>
                </x-slot:footer>
            </x-cart-alert>
        @elseif (empty($summary))
            <x-empty-state icon="🛒" title="Sepetiniz boş" description="Checkout için ürün ekleyin.">
                <x-button href="{{ url('/') }}">Ürünlere git</x-button>
            </x-empty-state>
        @else
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Son adım</p>
                <h1 class="mt-2 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-5xl">Siparişi tamamla</h1>
            </div>
            <form wire:submit="placeOrder" class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950 sm:p-8">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Teslimat adresi</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <x-input name="firstName" label="Ad" wire:model="firstName" required />
                        <x-input name="lastName" label="Soyad" wire:model="lastName" required />
                        <x-input name="email" type="email" label="E-posta" wire:model="email" autocomplete="email" required />
                        <x-input name="phone" type="tel" label="Telefon" wire:model="phone" autocomplete="tel" required />
                        <div class="sm:col-span-2">
                            <x-input name="lineOne" label="Adres" wire:model="lineOne" hint="Tam adres" required />
                        </div>
                        <x-input name="city" label="İl" wire:model="city" required />
                        <x-input name="state" label="İlçe" wire:model="state" hint="İsteğe bağlı" />
                        <x-input name="postcode" label="Posta kodu" wire:model="postcode" required />
                    </div>
                    <h2 class="mt-10 text-xl font-bold text-slate-900 dark:text-white">Ödeme</h2>
                    <div class="mt-4 space-y-3">
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-800/60 dark:bg-indigo-900/20">
                            <input type="radio" wire:model="paymentMethod" value="offline" class="mt-1 h-4 w-4 accent-indigo-600 focus:ring-indigo-500">
                            <span><strong class="block text-slate-900 dark:text-white">Manuel ödeme</strong><span class="text-sm text-slate-600 dark:text-slate-400">Sipariş oluşturulur</span></span>
                        </label>
                        @if ($stripeEnabled)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900/50">
                                <input type="radio" wire:model="paymentMethod" value="stripe" class="mt-1 h-4 w-4 accent-indigo-600 focus:ring-indigo-500">
                                <span><strong class="block text-slate-900 dark:text-white">Kart ile ödeme</strong><span class="text-sm text-slate-600 dark:text-slate-400">Stripe</span></span>
                            </label>
                        @endif
                    </div>
                    @error('paymentMethod')
                        <p class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950 lg:sticky lg:top-24 lg:self-start">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Sipariş özeti</h2>
                    <div class="mt-5 flex items-center justify-between text-sm text-slate-600 dark:text-slate-400"><span>{{ $summary['count'] }} ürün</span><span class="font-semibold text-slate-900 dark:text-white">₺</span></div>
                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between"><span class="text-slate-600 dark:text-slate-400">Ürünler (KDV hariç)</span><span class="font-semibold text-slate-900 dark:text-white">{{ number_format($summary['sub_total'] / 100, 2, ',', '.') }} ₺</span></div>
                        <div class="flex items-center justify-between"><span class="text-slate-600 dark:text-slate-400">KDV</span><span class="font-semibold text-slate-900 dark:text-white">{{ number_format($summary['tax_total'] / 100, 2, ',', '.') }} ₺</span></div>
                        <div class="flex items-center justify-between"><span class="text-slate-600 dark:text-slate-400">Kargo</span><span class="font-semibold text-slate-900 dark:text-white">{{ ($summary['shipping_total'] ?? 0) > 0 ? number_format($summary['shipping_total'] / 100, 2, ',', '.') . ' ₺' : 'Ücretsiz' }}</span></div>
                    </div>
                    <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4 dark:border-slate-700"><span class="font-semibold text-slate-700 dark:text-slate-200">Toplam</span><span class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ number_format($summary['total'] / 100, 2, ',', '.') }} ₺</span></div>
                    <x-button block class="mt-6" wire:loading.attr="disabled" type="submit">Siparişi tamamla</x-button>
                </aside>
            </form>
        @endif
    </section>
</div>