@props([
    'title' => null,
    'description' => 'ExportDepo — kaliteli ürünler, uygun fiyatlar ve hızlı teslimatla yeni nesil e-ticaret deneyimi.',
])

@php
    $appName = config('app.name', 'ExportDepo');
    $pageTitle = filled($title) ? $title . ' — ' . $appName : $appName;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="#4f46e5">

    <title>{{ $pageTitle }}</title>

    {{-- Tema tercihi ilk boyamadan önce uygulanır; böylece karanlık modda beyaz flaş (FOUC) olmaz. --}}
    <script>
        (() => {
            try {
                if (localStorage.getItem('theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (error) {
                // localStorage erişilemezse varsayılan (açık) tema kullanılır.
            }
        })();
    </script>

    {{-- Tipografi --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    {{-- Tailwind CSS v4 + Alpine.js (Vite derlemesi) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-900 antialiased font-sans dark:bg-slate-950 dark:text-slate-100">

    {{ $slot }}

    {{--
        Karanlık/Açık tema düğmesi.
        Phase 3'te header bileşeninin içine taşınacaktır; şimdilik sabit konumda,
        her sayfadan erişilebilir şekilde durur.
    --}}
    <button type="button"
            x-data
            @click="$store.theme.toggle()"
            :aria-label="$store.theme.dark ? 'Açık temaya geç' : 'Koyu temaya geç'"
            :title="$store.theme.dark ? 'Açık temaya geç' : 'Koyu temaya geç'"
            class="fixed bottom-4 right-4 z-50 inline-flex h-11 w-11 items-center justify-center rounded-full border border-slate-200 bg-white/90 text-slate-500 shadow-lg backdrop-blur-sm transition-colors hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-slate-700 dark:bg-slate-800/90 dark:text-slate-400 dark:hover:text-indigo-400">
        {{-- Koyu temaya geçme ikonu (açık temada görünür) --}}
        <svg x-show="!$store.theme.dark"
             x-cloak
             xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor"
             stroke-width="2"
             aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
        </svg>
        {{-- Açık temaya geçme ikonu (koyu temada görünür) --}}
        <svg x-show="$store.theme.dark"
             x-cloak
             xmlns="http://www.w3.org/2000/svg"
             class="h-5 w-5"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor"
             stroke-width="2"
             aria-hidden="true">
            <circle cx="12" cy="12" r="5" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
        </svg>
    </button>

    @livewireScripts
</body>
</html>
