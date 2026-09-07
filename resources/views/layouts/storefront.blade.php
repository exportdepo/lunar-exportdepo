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

                {{-- Cart + Auth + User --}}
                <div class="flex items-center gap-3">
                    @auth
                        <div class="relative" id="user-menu">
                            <button type="button" id="user-toggle" aria-expanded="false" aria-controls="user-dropdown" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 hover:text-indigo-600 transition-colors">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-xs font-bold">
                                    {{ mb_substr(auth()->user()->firstName() ?: auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="hidden sm:inline text-sm font-semibold">{{ auth()->user()->firstName() ?: auth()->user()->name }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="user-dropdown" hidden class="absolute right-0 mt-3 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
                                <a href="{{ url('/hesabim') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">👤 Hesabım</a>
                                <a href="{{ url('/hesabim/siparislerim') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">📦 Siparişlerim</a>
                                <div class="my-1 border-t border-slate-100"></div>
                                <form method="POST" action="{{ url('/cikis') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left rounded-lg px-3 py-2 text-sm text-rose-600 hover:bg-rose-50">🚪 Çıkış Yap</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ url('/giris') }}" class="hidden sm:inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-indigo-600 transition-colors">Giriş Yap</a>
                        <a href="{{ url('/kayit') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">Üye Ol</a>
                    @endauth
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
    <script>
        (() => {
            const toggle = document.getElementById('user-toggle');
            const dropdown = document.getElementById('user-dropdown');
            const container = document.getElementById('user-menu');
            if (!toggle || !dropdown || !container || toggle.dataset.ready) return;
            toggle.dataset.ready = 'true';
            toggle.addEventListener('click', () => {
                const isOpen = !dropdown.hidden;
                dropdown.hidden = isOpen;
                toggle.setAttribute('aria-expanded', String(!isOpen));
            });
            document.addEventListener('click', (event) => {
                if (!container.contains(event.target)) {
                    dropdown.hidden = true;
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        })();
    </script>
</body>
</html>
