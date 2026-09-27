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

    {{-- Ana içerik --}}
    <main id="main-content" tabindex="-1" class="flex-1 focus:outline-none">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <x-footer />
</x-layouts.app>
