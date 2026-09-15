<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'ExportDepo' }} — ExportDepo</title>

    {{-- Tailwind CSS (Play CDN - build gerektirmez) --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
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

    <style>
        /* Ön yüzde form elemanlarının (Checkbox, Radio, Input, Select) uyumlu, belirgin ve yumuşak gölgeli stilleri */

        /* Checkbox Stilleri */
        input[type="checkbox"] {
            opacity: 1 !important;
            visibility: visible !important;
            border: 1px solid #cbd5e1 !important; /* Slate-300 hafif gri kenarlık */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.08) !important; /* Yumuşak alt gölge */
            border-radius: 0.25rem !important;
            accent-color: #4f46e5 !important; /* Indigo-600 seçim rengi */
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        input[type="checkbox"]:hover {
            border-color: #94a3b8 !important; /* Hover'da hafifçe daha belirgin Slate-400 */
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.12) !important;
        }

        /* 1. Radyo Butonları (Kusursuz Yuvarlak) */
        input[type="radio"] {
            opacity: 1 !important;
            visibility: visible !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 9999px !important; /* Tam dairesellik garantisi */
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px -1px rgba(0, 0, 0, 0.08) !important;
            accent-color: #4f46e5 !important;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }
        input[type="radio"]:hover {
            border-color: #94a3b8 !important;
            box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.12) !important;
        }

        /* 2. Metin ve Girdi Alanları */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        input[type="number"],
        textarea {
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="tel"]:focus,
        input[type="number"]:focus,
        textarea:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2), 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            outline: none !important;
        }

        /* 3. Seçim Kutuları (Select) */
        select {
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
            cursor: pointer;
        }
        select:focus {
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2), 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            outline: none !important;
        }

        /* 4. Buton Etkileşimleri (Modern Hover, Active & Shadow) */
        button[type="submit"],
        button:not([disabled]),
        .btn-interactive {
            transition: transform 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease !important;
        }
        button[type="submit"]:hover,
        .bg-indigo-600:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25) !important;
        }
        button[type="submit"]:active,
        .bg-indigo-600:active {
            transform: translateY(0) scale(0.98);
            box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2) !important;
        }

        /* 5. Form Hata ve Doğrulama Mesajları */
        p.text-rose-600,
        .error-message {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.5rem;
            background-color: #fef2f2; /* Rose-50 */
            border-left: 3px solid #f43f5e; /* Rose-500 */
            border-radius: 0.375rem;
            font-size: 0.8125rem;
            font-weight: 500;
        }

        /* 6. Kart & Konteyner Çerçeve Gölge Dokunuşu */
        .rounded-2xl.bg-white {
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.03) !important;
            border-color: #e2e8f0 !important;
        }
    </style>

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
