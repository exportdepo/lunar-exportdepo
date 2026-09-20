{{--
    Uyarı/sonuç kartı bileşeni.

    Eski kullanım (geriye dönük uyumlu):
        <x-cart-alert :message="$cartError" />
        → `message` doluysa rose renkli hata şeridi render edilir; boşsa
          hiçbir şey çıkmaz. Home/Collection sayfaları bu modu kullanır.

    Panel modu (Phase 5+):
        <x-cart-alert type="success" icon="✓" title="Siparişiniz alındı">
            ...içerik...
            <x-slot:footer>...</x-slot:footer>
        </x-cart-alert>
        → `message` boşken slot doluysa geniş bilgi/başarı paneli açılır.

    type: error (varsayılan) | success | info
--}}
@props([
    'message' => null,
    'type' => 'error',
    'icon' => null,
    'title' => null,
])

@php
    $palette = [
        'error' => [
            'wrap' => 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300',
            'glyph' => '⚠',
        ],
        'success' => [
            'wrap' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300',
            'glyph' => '✓',
        ],
        'info' => [
            'wrap' => 'border-indigo-200 bg-indigo-50 text-indigo-800 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-300',
            'glyph' => 'ℹ',
        ],
    ];
    $resolved = $palette[$type] ?? $palette['error'];
    $glyph = $icon ?? $resolved['glyph'];
@endphp

@if (filled($message))
    {{-- Klasik hata şeridi: eski çağrılar için birebir aynı görünüm --}}
    <div role="alert"
         class="mb-6 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        <span>{{ $message }}</span>
    </div>
@elseif ($slot->isNotEmpty() || isset($header))
    {{-- Panel modu: başarı/bilgi kartı --}}
    <div role="status"
         {{ $attributes->merge(['class' => 'mx-auto max-w-2xl rounded-2xl border p-8 text-center sm:p-10 '.$resolved['wrap']]) }}>
        <div class="text-5xl" aria-hidden="true">{{ $glyph }}</div>

        @isset($header)
            <div class="mt-4">{{ $header }}</div>
        @elseif (filled($title))
            <h2 class="mt-4 text-2xl font-extrabold">{{ $title }}</h2>
        @endisset

        <div class="mt-3 text-sm leading-relaxed">{{ $slot }}</div>

        @isset($footer)
            <div class="mt-8">{{ $footer }}</div>
        @endisset
    </div>
@endif
