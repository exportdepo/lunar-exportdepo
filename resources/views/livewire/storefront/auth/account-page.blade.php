<div>
    @php
        $user = auth()->user();
        $recentOrders = $this->recentOrders();
        $totalOrders = $this->totalOrdersCount();
    @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Hesabım</p>
                <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Merhaba, {{ $user->firstName() ?: $user->name }} 👋</h1>
                <p class="mt-2 text-sm text-slate-500">{{ $user->email }}</p>
            </div>
            <form method="POST" action="{{ url('/cikis') }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Çıkış Yap
                </button>
            </form>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- SOL: Hesap bilgileri + son siparişler --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Hesap bilgileri kartı --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Hesap Bilgileri</h2>
                    <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Ad Soyad</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">E-posta</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Telefon</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $user->phone ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Üyelik Tarihi</dt>
                            <dd class="mt-1 font-semibold text-slate-900">{{ $user->created_at?->format('d.m.Y') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Son siparişler --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900">Son Siparişler</h2>
                        <a href="{{ url('/hesabim/siparislerim') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">Tümünü Gör →</a>
                    </div>
                    @if (empty($recentOrders))
                        <p class="mt-4 text-sm text-slate-500">Henüz siparişiniz bulunmuyor.</p>
                    @else
                        <ul class="mt-4 divide-y divide-slate-100">
                            @foreach ($recentOrders as $order)
                                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                    <div>
                                        <a href="{{ url('/siparis/' . $order['reference']) }}" class="font-semibold text-slate-900 hover:text-indigo-600">#{{ $order['reference'] }}</a>
                                        <p class="text-sm text-slate-500">{{ $order['placed_at'] }} · {{ $order['status_label'] }}</p>
                                    </div>
                                    <span class="font-bold text-slate-900">{{ number_format($order['total'] / 100, 2, ',', '.') }} ₺</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- SAĞ: İstatistikler + kısayollar --}}
            <aside class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Hesap Özeti</h2>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-indigo-600">{{ $totalOrders }}</div>
                        <div class="text-sm text-slate-500">Toplam Sipariş</div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-slate-900">Hızlı Erişim</h2>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="{{ url('/hesabim/siparislerim') }}" class="font-semibold text-slate-700 hover:text-indigo-600">📦 Tüm Siparişlerim</a></li>
                        <li><a href="{{ url('/sepet') }}" class="font-semibold text-slate-700 hover:text-indigo-600">🛒 Sepetim</a></li>
                        <li><a href="{{ url('/') }}" class="font-semibold text-slate-700 hover:text-indigo-600">🏠 Alışverişe Devam</a></li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>
</div>
