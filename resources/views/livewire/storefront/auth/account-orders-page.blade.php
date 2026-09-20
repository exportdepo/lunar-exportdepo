<div>
    @php
        $orders = $this->orders();
    @endphp

    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">Hesabım</p>
                <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">Siparişlerim</h1>
            </div>
            <a href="{{ url('/hesabim') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Hesabıma Dön</a>
        </div>

        @if (empty($orders))
            <x-empty-state icon="📦" title="Henüz sipariş yok" description="İlk siparişinizi vererek başlayabilirsiniz.">
                <x-button href="{{ url('/') }}">Alışverişe Başla</x-button>
            </x-empty-state>
        @else
            <div class="space-y-3">
                @foreach ($orders as $order)
                    <a href="{{ url('/siparis/' . $order['reference']) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="font-bold text-slate-900 dark:text-white">#{{ $order['reference'] }}</div>
                                <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $order['placed_at'] }}</div>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold text-white" style="background-color: {{ $order['status_color'] }};">
                                <span class="h-1.5 w-1.5 rounded-full bg-white/80"></span>
                                {{ $order['status_label'] }}
                            </span>
                            <span class="text-lg font-extrabold text-slate-900 dark:text-white">{{ number_format($order['total'] / 100, 2, ',', '.') }} ₺</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>