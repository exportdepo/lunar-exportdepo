<div>
    @php
        $user = auth()->user();
        $recentOrders = $this->recentOrders();
        $totalOrders = $this->totalOrdersCount();
    @endphp

    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Hesabım</p>
                <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">Merhaba, {{ $user->firstName() ?: $user->name }}</h1>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
            </div>
            <form method="POST" action="{{ url('/cikis') }}" class="mt-4 sm:mt-0">@csrf
                <x-button type="submit" variant="outline" size="sm">Çıkış Yap</x-button>
            </form>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Hesap Bilgileri</h2>
                    <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                        <div><dt class="text-slate-500 dark:text-slate-400">Ad Soyad</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $user->name }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">E-posta</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $user->email }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Telefon</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $user->phone ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500 dark:text-slate-400">Üyelik Tarihi</dt><dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $user->created_at->format("d.m.Y") }}</dd></div>
                    </dl>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Son Siparişler</h2>
                        <a href="{{ url('/hesabim/siparislerim') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">Tümünü Göster</a>
                    </div>
                    @if (empty($recentOrders))
                        <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Henüz sipariş yok.</p>
                    @else
                        <ul class="mt-4 divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($recentOrders as $order)
                                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                    <div>
                                        <a href="{{ url('/siparis/' . $order['reference']) }}" class="font-semibold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">#{{ $order['reference'] }}</a>
                                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $order['placed_at'] }} | {{ $order['status_label'] }}</p>
                                    </div>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ number_format($order['total'] / 100, 2, ',', '.') }} ₺</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
            <aside class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Hesap Özeti</h2>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">{{ $totalOrders }}</div>
                        <div class="text-sm text-slate-500 dark:text-slate-400">Toplam Sipariş</div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Hızlı Erişim</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ url('/hesabim/siparislerim') }}" class="font-semibold text-slate-700 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">Tüm Siparişlerim</a></li>
                        <li><a href="{{ url('/sepet') }}" class="font-semibold text-slate-700 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">Sepetim</a></li>
                        <li><a href="{{ url('/') }}" class="font-semibold text-slate-700 hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">Alışverişe Devam</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>
</div>