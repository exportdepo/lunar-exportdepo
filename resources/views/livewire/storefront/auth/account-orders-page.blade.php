<div>
    @php
        $orders = $this->orders();
    @endphp

    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Hesabım</p>
                <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Siparişlerim</h1>
            </div>
            <a href="{{ url('/hesabim') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Hesabıma Dön</a>
        </div>

        @if (empty($orders))
            <div class="bg-white border border-dashed border-slate-300 rounded-2xl p-14 text-center">
                <div class="text-5xl mb-4">📦</div>
                <h2 class="text-xl font-semibold text-slate-800">Henüz siparişiniz yok</h2>
                <p class="mt-2 text-slate-500">İlk siparişinizi vererek başlayabilirsiniz.</p>
                <a href="{{ url('/') }}" class="mt-6 inline-flex rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Alışverişe Başla</a>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($orders as $order)
                    <a href="{{ url('/siparis/' . $order['reference']) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-900">#{{ $order['reference'] }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $order['placed_at'] }}</div>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $order['status_color'] }};">
                                <span class="h-1.5 w-1.5 rounded-full bg-white/80"></span>
                                {{ $order['status_label'] }}
                            </span>
                            <span class="text-lg font-extrabold text-slate-900">{{ number_format($order['total'] / 100, 2, ',', '.') }} ₺</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
