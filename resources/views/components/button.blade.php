{{--
    ExportDepo atomik buton bileşeni.

    Kullanım:
        <x-button>Sepete Ekle</x-button>
        <x-button variant="outline" size="sm">Detaylar</x-button>
        <x-button href="/sepet">Sepete Git</x-button>
        <x-button wire:click="addToCart(5)" wire:loading.attr="disabled">Sepete Ekle</x-button>

    Varyantlar : primary (varsayılan), secondary, outline, ghost, danger, inverse, glass
    Boyutlar   : sm, md (varsayılan), lg
    `href` verilirse <a> olarak, aksi halde <button> olarak render edilir.
    Ek tüm nitelikler ($attributes) elemana olduğu gibi aktarılır (wire:*, x-* vb.).
--}}
@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'block' => false,
    'disabled' => false,
    'loading' => false,
])

@php
    $baseClasses = 'inline-flex select-none items-center justify-center gap-2 whitespace-nowrap rounded-lg font-semibold transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-slate-950 disabled:pointer-events-none disabled:opacity-50';

    $variantClasses = [
        'primary' => 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700 hover:shadow-md active:bg-indigo-800 dark:bg-indigo-500 dark:hover:bg-indigo-400 dark:active:bg-indigo-300 dark:focus-visible:ring-indigo-400',
        'secondary' => 'bg-slate-900 text-white shadow-sm hover:bg-slate-700 active:bg-slate-800 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white dark:active:bg-slate-300',
        'outline' => 'border border-slate-300 bg-white text-slate-700 shadow-sm hover:border-slate-400 hover:bg-slate-50 active:bg-slate-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-slate-500 dark:hover:bg-slate-700 dark:active:bg-slate-600',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 active:bg-slate-200 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white',
        'inverse' => 'bg-white text-indigo-700 shadow-sm hover:bg-slate-100 active:bg-slate-200 dark:bg-white dark:text-indigo-700 dark:hover:bg-slate-100',
        'glass' => 'border border-white/30 bg-white/10 text-white backdrop-blur-sm hover:bg-white/20 active:bg-white/25',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700 active:bg-rose-800 dark:bg-rose-500 dark:hover:bg-rose-400 dark:active:bg-rose-300',
    ];

    $sizeClasses = [
        'sm' => 'h-9 px-3.5 text-sm',
        'md' => 'h-11 px-5 text-sm',
        'lg' => 'h-12 px-6 text-base',
    ];

    $classes = trim(
        ($variantClasses[$variant] ?? $variantClasses['primary']).' '.
        ($sizeClasses[$size] ?? $sizeClasses['md']).
        ($block ? ' w-full' : '')
    );
@endphp

@if (filled($href))
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses.' '.$classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
            {{ $attributes->merge(['class' => $baseClasses.' '.$classes]) }}
            @if ($disabled) disabled @endif>
        @if ($loading)
            <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
        @endif

        {{ $slot }}
    </button>
@endif
