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

    {{-- Erişilebilirlik: klavye kullanıcıları için sayfa içeriğine atlama bağlantısı (ilk Tab'da görünür olur) --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-indigo-600 focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white focus:shadow-lg">
        İçeriğe geç
    </a>

    {{ $slot }}

    @livewireScripts
</body>
</html>
