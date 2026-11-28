{{--
    Vitrin (storefront) iskeleti.

    Ana HTML iskeleti `components/layouts/app.blade.php` anonim bileşenindedir
    (meta etiketleri, @vite direktifi, Alpine entegrasyonu ve karanlık mod altyapısı).
    Üst navigasyon `components/header.blade.php` bileşenindedir; sepet çekmecesi
    `components/cart-drawer.blade.php` kabuğuyla, `livewire:storefront.cart-indicator`
    bileşeni üzerinden yönetilir. Footer (güven bandı, bülten, link grid'i ve
    sipariş takibi) `components/footer.blade.php` bileşenindedir.
--}}
<x-layouts.app :title="$title ?? null" :description="$description ?? null" :ogImage="$ogImage ?? null">
    <x-header />

    {{-- Skip to main content link (WCAG 2.4.1) --}}
    <a href="#main-content"
       class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-indigo-600 focus:text-white focus:rounded-lg focus:font-semibold focus:shadow-lg"
       tabindex="0">
        Ana içeriğe atla
    </a>

    {{-- Ana içerik — `scroll-mt-20` yapışkan header'ın (h-16) altında kalan
         kısımların gizlenmesini engeller (WCAG 2.4.11 Focus Not Obscured). --}}
    <main id="main-content" tabindex="-1" class="flex-1 scroll-mt-20 focus:outline-none">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <x-footer />
</x-layouts.app>
