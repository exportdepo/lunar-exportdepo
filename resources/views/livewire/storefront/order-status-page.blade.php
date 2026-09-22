<div>
    @php
        // Vitrin tek para birimi (TRY) ile çalışır; sipariş farklı bir birimde
        // oluşturulmuşsa tutarlar o birimin simgesiyle gösterilir.
        $currencySymbol = ['TRY' => '₺', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'][$order->currency_code] ?? $order->currency_code;
    @endphp
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        {{-- Üst başlık --}}
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Sipariş Takibi</p>
                <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">#{{ $order->reference }}</h1>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-white shadow-sm {{ $this->statusBadgeClass() }}">
                <span class="h-2 w-2 rounded-full bg-white/80"></span>
                {{ $this->statusLabel() }}
            </span>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- SOL: Sipariş özeti + ürünler --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Özet kartı --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Sipariş Özeti</h2>
                    <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Sipariş Tarihi</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $order->placed_at?->format('d.m.Y H:i') ?? $order->created_at?->format('d.m.Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Ödeme Yöntemi</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $this->paymentMethod() }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Para Birimi</dt>
                            <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $order->currency_code }} ({{ $currencySymbol }})</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Toplam Tutar</dt>
                            <dd class="mt-1 text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ number_format(($order->total?->value ?? 0) / 100, 2, ',', '.') }} {{ $currencySymbol }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Ürünler --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Ürünler</h2>
                    @php
                        $lines = $this->lines();
                    @endphp
                    @if (empty($lines))
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Bu siparişte ürün bulunamadı.</p>
                    @else
                        <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($lines as $line)
                                <li class="flex items-start justify-between gap-4 py-3">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ $line['name'] }}</p>
                                        @if (! empty($line['options']))
                                            <p class="mt-0.5 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $line['options'] }}</p>
                                        @endif
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $line['quantity'] }} adet × {{ number_format($line['unit_price'] / 100, 2, ',', '.') }} {{ $currencySymbol }}</p>
                                    </div>
                                    <span class="shrink-0 font-bold text-slate-900 dark:text-white">{{ number_format($line['total'] / 100, 2, ',', '.') }} {{ $currencySymbol }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- SAĞ: Adres + CTA --}}
            @php
                $billing = $order->billingAddress;
                $shipping = $order->shippingAddress ?? $billing;
            @endphp
            <aside class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Teslimat Adresi</h2>
                    @if ($shipping)
                        <address class="mt-4 not-italic text-sm leading-6 text-slate-700 dark:text-slate-300">
                            <strong class="text-slate-900 dark:text-white">{{ $shipping->first_name }} {{ $shipping->last_name }}</strong><br>
                            {{ $shipping->line_one }}<br>
                            {{ $shipping->city }}{{ $shipping->state ? ', ' . $shipping->state : '' }} {{ $shipping->postcode }}<br>
                            @if ($shipping->contact_phone)
                                <span class="text-slate-500 dark:text-slate-400">Tel: {{ $shipping->contact_phone }}</span>
                            @endif
                        </address>
                    @else
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Bu siparişte teslimat adresi bulunmuyor.</p>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Fatura Adresi</h2>
                    @if ($billing)
                        <address class="mt-4 not-italic text-sm leading-6 text-slate-700 dark:text-slate-300">
                            <strong class="text-slate-900 dark:text-white">{{ $billing->first_name }} {{ $billing->last_name }}</strong><br>
                            {{ $billing->line_one }}<br>
                            {{ $billing->city }}{{ $billing->state ? ', ' . $billing->state : '' }} {{ $billing->postcode }}
                        </address>
                    @else
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Bu siparişte fatura adresi bulunmuyor.</p>
                    @endif
                </div>

                <a href="{{ url('/') }}" class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700 transition-colors">
                    Alışverişe Dön
                </a>
            </aside>
        </div>
    </section>
</div>
