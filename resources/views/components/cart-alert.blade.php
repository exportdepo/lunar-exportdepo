{{--
    Sepet işlemi hatalarını gösteren uyarı bileşeni.

    Kullanım: <x-cart-alert :message="$cartError" />
    `message` boşsa hiçbir şey render edilmez.
--}}
@props([
    'message' => null,
])

@if (filled($message))
    <div role="alert"
         class="mb-6 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
        </svg>
        <span>{{ $message }}</span>
    </div>
@endif
