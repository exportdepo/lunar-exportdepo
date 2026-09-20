{{--
    Vitrin (storefront) iskeleti.

    Ana HTML iskeleti `components/layouts/app.blade.php` anonim bileşenindedir
    (meta etiketleri, @vite direktifi, Alpine entegrasyonu ve karanlık mod altyapısı).
    Üst navigasyon `components/header.blade.php` bileşenindedir; sepet çekmecesi
    `components/cart-drawer.blade.php` kabuğuyla, `livewire:storefront.cart-indicator`
    bileşeni üzerinden yönetilir.

    Phase 4'te sayfa şablonları (ana sayfa, PLP, PDP) bu iskelet üzerine kurulacaktır.
--}}
<x-layouts.app :title="$title ?? null">
    <x-header />

    {{-- Ana içerik --}}
    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="mt-16 bg-slate-900 text-slate-300">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                <div>
                    <div class="mb-3 flex items-center gap-2">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-sm text-white">🌙</span>
                        <span class="font-semibold text-white">ExportDepo</span>
                    </div>
                    <p class="text-sm text-slate-400">Türkiye'nin yeni nesil e-ticaret deneyimi.</p>
                </div>
                <div>
                    <h4 class="mb-3 text-sm font-medium text-white">Kategoriler</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/koleksiyon/elektronik') }}" class="transition-colors hover:text-white">Elektronik</a></li>
                        <li><a href="{{ url('/koleksiyon/giyim') }}" class="transition-colors hover:text-white">Giyim</a></li>
                        <li><a href="{{ url('/koleksiyon/ev-yasam') }}" class="transition-colors hover:text-white">Ev &amp; Yaşam</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-3 text-sm font-medium text-white">Yardım</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/sepet') }}" class="transition-colors hover:text-white">Sepetim</a></li>
                        <li><a href="{{ url('/exportdepo/login') }}" class="transition-colors hover:text-white">Yönetim Paneli</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 flex flex-col justify-between gap-2 border-t border-slate-800 pt-6 text-xs text-slate-500 sm:flex-row">
                <span>© {{ date('Y') }} ExportDepo. Tüm hakları saklıdır.</span>
                <span>🇹🇷 Türkiye — TRY</span>
            </div>
        </div>
    </footer>
</x-layouts.app>
