<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'ExportDepo' }} — ExportDepo</title>

    {{-- Tailwind CSS (Play CDN - build gerektirmez) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Türkçe font + bazı Tailwind config
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>

    {{-- Livewire styles --}}
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col font-sans">

    {{-- Header --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-lg font-bold shadow-sm group-hover:scale-105 transition-transform">
                        🌙
                    </span>
                    <span class="text-lg font-semibold text-slate-900">ExportDepo</span>
                </a>

                {{-- Navigation --}}
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                    <a href="{{ url('/') }}" class="hover:text-indigo-600 transition-colors">Anasayfa</a>
                    <a href="{{ url('/koleksiyon/elektronik') }}" class="hover:text-indigo-600 transition-colors">Elektronik</a>
                    <a href="{{ url('/koleksiyon/giyim') }}" class="hover:text-indigo-600 transition-colors">Giyim</a>
                    <a href="{{ url('/koleksiyon/ev-yasam') }}" class="hover:text-indigo-600 transition-colors">Ev & Yaşam</a>
                </nav>

                {{-- Cart + User --}}
                <div class="flex items-center gap-3">
                    <livewire:storefront.cart-indicator />
                </div>
            </div>
        </div>
    </header>

    {{-- Main content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-sm">🌙</span>
                        <span class="text-white font-semibold">ExportDepo</span>
                    </div>
                    <p class="text-sm text-slate-400">Türkiye'nin yeni nesil e-ticaret deneyimi.</p>
                </div>
                <div>
                    <h4 class="text-white font-medium mb-3 text-sm">Kategoriler</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/koleksiyon/elektronik') }}" class="hover:text-white transition-colors">Elektronik</a></li>
                        <li><a href="{{ url('/koleksiyon/giyim') }}" class="hover:text-white transition-colors">Giyim</a></li>
                        <li><a href="{{ url('/koleksiyon/ev-yasam') }}" class="hover:text-white transition-colors">Ev & Yaşam</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-medium mb-3 text-sm">Yardım</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/sepet') }}" class="hover:text-white transition-colors">Sepetim</a></li>
                        <li><a href="{{ url('/exportdepo/login') }}" class="hover:text-white transition-colors">Yönetim Paneli</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-slate-800 text-xs text-slate-500 flex flex-col sm:flex-row justify-between gap-2">
                <span>© {{ date('Y') }} ExportDepo. Tüm hakları saklıdır.</span>
                <span>🇹🇷 Türkiye — TRY</span>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
