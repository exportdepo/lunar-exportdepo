{{--
    Ana sayfa (Phase 4a).

    Akış: tam ekran Hero -> Öne Çıkan Ürünler -> Kategoriler -> Tüm Ürünler.
    Header arama kutusundan `?q=` geldiğinde hero/kategoriler gizlenir ve
    sayfa doğrudan arama sonuçlarıyla açılır (results-first).
--}}
<div>
    @php
        // Öne çıkanlar: ilk 8 ürün. Kalan ürünler "Tüm Ürünler" bölümünde listelenir.
        $featuredProducts = array_slice($products, 0, 8);
        $remainingProducts = array_slice($products, 8);
    @endphp

    @unless (filled($q))
        {{-- Hero: tam ekran banner --}}
        <section class="relative isolate overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white">
            <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-white/20 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-16 h-80 w-80 rounded-full bg-indigo-300/30 blur-3xl" aria-hidden="true"></div>

            <div class="relative mx-auto flex min-h-[78vh] max-w-7xl flex-col items-center justify-center px-4 py-20 text-center sm:px-6 sm:py-24 lg:px-8">
                <span class="mb-6 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-widest text-white/90">
                    🇹🇷 Türkiye'nin yeni nesil e-ticaret deneyimi
                </span>

                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                    ExportDepo'ya Hoş Geldiniz
                </h1>

                <p class="mx-auto mt-5 max-w-2xl text-base text-indigo-100 sm:text-lg">
                    Kaliteli ürünler, uygun fiyatlar ve hızlı teslimat. Aradığınız her şey tek bir yerde.
                </p>

                <div class="mt-9 flex w-full flex-col items-center justify-center gap-3 sm:w-auto sm:flex-row">
                    <x-button size="lg" variant="inverse" href="#one-cikanlar" class="w-full sm:w-auto">
                        Alışverişe Başla
                    </x-button>
                    <x-button size="lg" variant="glass" href="#kategoriler" class="w-full sm:w-auto">
                        Kategorileri Gör
                    </x-button>
                </div>

                <dl class="mt-14 grid w-full max-w-3xl grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 backdrop-blur-sm">
                        <dt class="text-sm font-semibold">🚚 Ücretsiz Kargo</dt>
                        <dd class="mt-1 text-xs text-indigo-100">Tüm siparişlerde</dd>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 backdrop-blur-sm">
                        <dt class="text-sm font-semibold">🔒 Güvenli Ödeme</dt>
                        <dd class="mt-1 text-xs text-indigo-100">SSL korumalı altyapı</dd>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 backdrop-blur-sm">
                        <dt class="text-sm font-semibold">⚡ Hızlı Teslimat</dt>
                        <dd class="mt-1 text-xs text-indigo-100">Aynı gün kargo</dd>
                    </div>
                </dl>
            </div>
        </section>

        {{-- Öne çıkan ürünler --}}
        <section id="one-cikanlar" class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Öne Çıkan Ürünler</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Sizin için seçtiğimiz popüler ürünler</p>
                </div>
            </div>

            <x-cart-alert :message="$cartError" />

            @if (empty($featuredProducts))
                <x-empty-state title="Henüz ürün yok" description="Demo veri yüklenmedi." />
            @else
                @include('livewire.storefront.partials.product-grid', [
                    'gridProducts' => $featuredProducts,
                    'wishlistIds' => $wishlistIds,
                    'gridClasses' => 'grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5',
                ])
            @endif
        </section>

        {{-- Kategoriler --}}
        @if (!empty($collections))
            <section id="kategoriler" class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Kategoriler</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">İlgilendiğiniz alana göz atın</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    @foreach ($collections as $collection)
                        <a href="{{ url('/koleksiyon/' . ($collection['url'] ?? $collection['id'])) }}"
                           class="group relative aspect-[4/3] overflow-hidden rounded-2xl border border-slate-200 shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl dark:border-slate-800">
                            @if ($collection['image_url'])
                                <img src="{{ $collection['image_url'] }}"
                                     alt="{{ $collection['name'] }}"
                                     loading="lazy"
                                     class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <span class="absolute inset-0 flex items-center justify-center bg-slate-100 text-5xl dark:bg-slate-800" aria-hidden="true">🗂️</span>
                            @endif

                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-900/20 to-transparent"></div>

                            <div class="absolute inset-x-0 bottom-0 p-5 text-white">
                                <h3 class="text-xl font-bold">{{ $collection['name'] }}</h3>
                                <p class="mt-1 line-clamp-1 text-sm text-white/80">{{ $collection['description'] }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Kalan ürünler --}}
        @if (!empty($remainingProducts))
            <section id="urunler" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Tüm Ürünler</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ count($products) }} ürün</p>
                    </div>
                </div>

                <x-cart-alert :message="$cartError" />

                @include('livewire.storefront.partials.product-grid', [
                    'gridProducts' => $remainingProducts,
                    'wishlistIds' => $wishlistIds,
                    'gridClasses' => 'grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5',
                ])
            </section>
        @endif
    @else
        {{-- Arama sonuçları (results-first) --}}
        <section id="urunler" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white">«{{ $q }}» için arama sonuçları</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ count($products) }} sonuç bulundu</p>
                </div>
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition-colors hover:border-slate-300 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:text-indigo-400">
                    ✕ Aramayı temizle
                </a>
            </div>

            <x-cart-alert :message="$cartError" />

            @if (empty($products))
                <x-empty-state icon="🔍"
                               title="Aramanızla eşleşen ürün bulunamadı"
                               description="Farklı anahtar kelimelerle tekrar deneyin.">
                    <x-button href="{{ url('/') }}" size="sm">Tüm ürünlere dön</x-button>
                </x-empty-state>
            @else
                @include('livewire.storefront.partials.product-grid', [
                    'gridProducts' => $products,
                    'wishlistIds' => $wishlistIds,
                    'gridClasses' => 'grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5',
                ])
            @endif
        </section>
    @endunless
</div>
