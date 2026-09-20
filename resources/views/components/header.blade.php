{{--
    ExportDepo yapiskan (sticky) ust navigasyon bileseni.

    - Logo, tema dugmesi, uyelik alani, istek listesi ve sepet rozetini icerir;
      arama ikonla acilir ve ekran ortasinda modal (popup) olarak belirir.
    - Masaustu yerlesim: logo solda, nav ortada (absolute ortalama), aksiyonlar
      sagda; ikon sirasi: arama, tema, istek listesi, sepet, kullanici en sagda.
    - Mobilde hamburger menu (Alpine `mobileOpen` durumu) ile acilir panel.
    - Sepet dugmesi ve cekmece `livewire:storefront.cart-indicator` bilesenindedir;
      acilip kapanma `$store.cartDrawer` Alpine store'u ile yonetilir.
    - Not: panelde `backdrop-blur` KULLANILMAZ; aksi halde backdrop-filter,
      icindeki `fixed` cekmeceyi viewport yerine header'a gore konumlandirir.
--}}
<header class="sticky top-0 z-40 border-b border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div x-data="{ mobileOpen: false, searchOpen: false }" @keydown.escape.window="mobileOpen = false; searchOpen = false" class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center gap-4">

            {{-- Logo --}}
            <a href="{{ url('/') }}" aria-label="ExportDepo ana sayfa" class="group flex shrink-0 items-center gap-2">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-lg font-bold text-white shadow-sm transition-transform group-hover:scale-105">🌙</span>
                <span class="text-lg font-semibold text-slate-900 dark:text-white">ExportDepo</span>
            </a>

            {{-- Masaüstü navigasyon --}}
            <nav aria-label="Ana menü" class="absolute top-1/2 left-1/2 hidden -translate-x-1/2 -translate-y-1/2 items-center gap-5 whitespace-nowrap text-sm font-medium text-slate-600 dark:text-slate-300 lg:flex">
                <a href="{{ url('/koleksiyon/elektronik') }}" class="transition-colors hover:text-indigo-600 dark:hover:text-indigo-400">Elektronik</a>
                <a href="{{ url('/koleksiyon/giyim') }}" class="transition-colors hover:text-indigo-600 dark:hover:text-indigo-400">Giyim</a>
                <a href="{{ url('/koleksiyon/ev-yasam') }}" class="transition-colors hover:text-indigo-600 dark:hover:text-indigo-400">Ev &amp; Yaşam</a>
            </nav>

            {{-- Aksiyonlar --}}
            <div class="ml-auto flex shrink-0 items-center gap-1 sm:gap-2">

                {{-- Arama: ikon header altındaki paneli açar --}}
                <button type="button"
                        @click="searchOpen = !searchOpen; mobileOpen = false; $nextTick(() => $refs.searchInput.focus())"
                        :aria-expanded="searchOpen"
                        aria-controls="header-search-modal"
                        aria-label="Arama yap"
                        title="Arama yap"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </button>

                {{-- Tema düğmesi (mobilde mobil menüde) --}}
                <button type="button"
                        x-data
                        @click="$store.theme.toggle()"
                        :aria-label="$store.theme.dark ? 'Açık temaya geç' : 'Koyu temaya geç'"
                        :title="$store.theme.dark ? 'Açık temaya geç' : 'Koyu temaya geç'"
                        class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-indigo-400 sm:inline-flex">
                    <svg x-show="!$store.theme.dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                    <svg x-show="$store.theme.dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                    </svg>
                </button>

                {{-- İstek listesi + sepet (Livewire) --}}
                <livewire:storefront.wishlist-indicator />
                <livewire:storefront.cart-indicator />

                {{-- Üyelik: kullanıcı menüsü --}}
                @auth
                    <div class="relative hidden shrink-0 sm:block" x-data="{ open: false }">
                        <button type="button"
                                @click="open = !open"
                                aria-controls="user-dropdown"
                                :aria-expanded="open"
                                class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-slate-600 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-xs font-bold text-white">
                                {{ mb_substr(auth()->user()->firstName() ?: auth()->user()->name, 0, 1) }}
                            </span>
                            <span class="hidden text-sm font-semibold xl:inline">{{ auth()->user()->firstName() ?: auth()->user()->name }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="user-dropdown"
                             x-show="open"
                             x-transition.opacity.duration.150ms
                             @click.outside="open = false"
                             x-cloak
                             class="absolute right-0 mt-3 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-700 dark:bg-slate-800">
                            <a href="{{ url('/hesabim') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">👤 Hesabım</a>
                            <a href="{{ url('/hesabim/siparislerim') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">📦 Siparişlerim</a>
                            <a href="{{ url('/istek-listesi') }}" class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700">❤️ İstek Listem</a>
                            <div class="my-1 border-t border-slate-100 dark:border-slate-700"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">🚪 Çıkış Yap</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ url('/giris') }}" class="hidden shrink-0 items-center rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 hover:text-indigo-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-indigo-400 lg:inline-flex">Giriş Yap</a>
                    <x-button size="sm" href="{{ url('/kayit') }}" class="hidden shrink-0 sm:inline-flex">Üye Ol</x-button>
                @endauth

                {{-- Hamburger (mobil) --}}
                <button type="button"
                        @click="mobileOpen = !mobileOpen; searchOpen = false"
                        :aria-expanded="mobileOpen"
                        aria-controls="mobile-menu"
                        aria-label="Menüyü aç/kapat"
                        class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800 lg:hidden">
                    <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileOpen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Arama popup: ikon ile açılır, ekran ortasında modal olarak belirir --}}
        <div id="header-search-modal"
             x-show="searchOpen"
             x-cloak
             x-transition.opacity.duration.200ms
             @click.self="searchOpen = false"
             role="dialog"
             aria-modal="true"
             aria-label="Arama"
             class="fixed inset-0 z-50 flex items-start justify-center bg-slate-950/60 px-4 pt-24 sm:pt-32">
            <div x-show="searchOpen"
                 x-cloak
                 x-transition.scale.95.duration.200ms
                 class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-800">
                <form action="{{ url('/') }}" method="GET" role="search" class="relative">
                    <input type="search"
                           name="q"
                           value="{{ request('q') }}"
                           placeholder="Ürün ara..."
                           aria-label="Ürün ara"
                           x-ref="searchInput"
                           class="w-full border-0 bg-slate-100 py-3.5 pl-12 pr-4 text-base text-slate-900 placeholder:text-slate-400 transition-colors focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:bg-slate-700 dark:text-slate-100 dark:placeholder:text-slate-500">
                    <button type="submit" aria-label="Ara" class="absolute left-0 top-0 z-10 inline-flex h-full w-12 items-center justify-center text-slate-400 transition-colors hover:text-indigo-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:hover:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </button>
                </form>
                <div class="border-t border-slate-100 px-4 py-2.5 text-xs text-slate-400 dark:border-slate-700 dark:text-slate-500">
                    Aramak için <span class="font-semibold">Enter</span>'a basın · Kapatmak için <span class="font-semibold">Esc</span>'e basın
                </div>
            </div>
        </div>

        {{-- Mobil menü paneli --}}
        <div id="mobile-menu"
             x-show="mobileOpen"
             x-cloak
             x-transition.opacity.duration.200ms
             class="border-t border-slate-100 py-4 dark:border-slate-800 lg:hidden">

            {{-- Mobil navigasyon --}}
            <nav aria-label="Mobil menü" class="flex flex-col gap-1 text-sm font-medium text-slate-700 dark:text-slate-300">
                <a href="{{ url('/koleksiyon/elektronik') }}" class="rounded-lg px-3 py-2 transition-colors hover:bg-slate-100 dark:hover:bg-slate-800">🎧 Elektronik</a>
                <a href="{{ url('/koleksiyon/giyim') }}" class="rounded-lg px-3 py-2 transition-colors hover:bg-slate-100 dark:hover:bg-slate-800">👕 Giyim</a>
                <a href="{{ url('/koleksiyon/ev-yasam') }}" class="rounded-lg px-3 py-2 transition-colors hover:bg-slate-100 dark:hover:bg-slate-800">🏡 Ev &amp; Yaşam</a>
            </nav>

            {{-- Mobil tema düğmesi --}}
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800 sm:hidden">
                <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Görünüm</span>
                <button type="button"
                        x-data
                        @click="$store.theme.toggle()"
                        class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    <svg x-show="!$store.theme.dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                    </svg>
                    <svg x-show="$store.theme.dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
                    </svg>
                    <span x-text="$store.theme.dark ? 'Açık tema' : 'Koyu tema'"></span>
                </button>
            </div>

            {{-- Mobil üyelik --}}
            <div class="mt-4 flex flex-col gap-1 border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
                @auth
                    <a href="{{ url('/hesabim') }}" class="rounded-lg px-3 py-2 text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">👤 Hesabım</a>
                    <a href="{{ url('/hesabim/siparislerim') }}" class="rounded-lg px-3 py-2 text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">📦 Siparişlerim</a>
                    <a href="{{ url('/istek-listesi') }}" class="rounded-lg px-3 py-2 text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">❤️ İstek Listem</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">🚪 Çıkış Yap</button>
                    </form>
                @else
                    <a href="{{ url('/giris') }}" class="rounded-lg px-3 py-2 text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-slate-800">Giriş Yap</a>
                    <x-button block href="{{ url('/kayit') }}" class="mt-1">Üye Ol</x-button>
                @endauth
            </div>
        </div>
    </div>
</header>
