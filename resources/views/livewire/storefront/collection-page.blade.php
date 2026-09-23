{{--
    Kategori listeleme sayfası (PLP) — Phase 4b.

    Akış: kompakt koleksiyon başlığı -> sol sticky filtre sütunu + sağ ürün ızgarası.
    Filtreler (arama, kategori, varyant özellik/değer) mevcut Livewire API'leri
    (wire:model + clearFilters) ile çalışır; ürünler ortak `product-grid` partial'ı
    üzerinden Phase 2 ürün kartlarıyla çizilir.
    Mobilde filtreler Alpine paneliyle açılır/kapanır.
--}}
<div>
    {{-- Koleksiyon başlık şeridi --}}
    <section class="border-b border-slate-800 bg-slate-900 text-white">
        <div class="anim-fade-up mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-slate-300 transition-colors hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Tüm ürünler
            </a>

            <div class="mt-4 grid gap-6 md:grid-cols-[minmax(0,1fr)_16rem] md:items-center">
                <div>
                    <p class="mb-2 text-sm font-semibold uppercase tracking-widest text-indigo-300">Koleksiyon</p>
                    <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $collection['name'] }}</h1>
                    <p class="mt-3 max-w-2xl text-slate-300">{{ $collection['description'] }}</p>
                </div>

                @if ($collection['image_url'])
                    <img src="{{ $collection['image_url'] }}"
                         alt="{{ $collection['name'] }}"
                         loading="lazy"
                         decoding="async"
                         width="800"
                         height="600"
                         class="aspect-4/3 w-full rounded-2xl object-cover shadow-2xl">
                @endif
            </div>
        </div>
    </section>

    <div class="stagger-in mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{ mobileFiltersOpen: false }" @keydown.escape.window="mobileFiltersOpen = false">
        {{-- Mobil filtre düğmesi --}}
        <div class="mb-4 lg:hidden">
            <button type="button"
                    @click="mobileFiltersOpen = !mobileFiltersOpen"
                    :aria-expanded="mobileFiltersOpen"
                    aria-controls="plp-filters"
                    class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filtreler
                @php
                    $activeOptionFilterCount = 0;
                    foreach ($optionFilters as $activeValues) {
                        foreach ((array) $activeValues as $activeChecked) {
                            if ($activeChecked !== false && $activeChecked !== null && $activeChecked !== '') {
                                $activeOptionFilterCount++;
                            }
                        }
                    }
                    $activeBrandFilterCount = 0;
                    foreach ($brandFilters as $activeChecked) {
                        if ($activeChecked !== false && $activeChecked !== null && $activeChecked !== '') {
                            $activeBrandFilterCount++;
                        }
                    }
                    $activeTagFilterCount = 0;
                    foreach ($tagFilters as $activeChecked) {
                        if ($activeChecked !== false && $activeChecked !== null && $activeChecked !== '') {
                            $activeTagFilterCount++;
                        }
                    }
                    $activeFilterCount = (int) filled($searchTerm) + (int) (bool) $selectedCategoryId + $activeBrandFilterCount + $activeTagFilterCount + $activeOptionFilterCount + count(array_filter($attributeFilters));
                @endphp
                @if ($activeFilterCount > 0)
                    <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1.5 text-xs font-bold text-indigo-700 shadow-md ring-1 ring-indigo-300 dark:text-indigo-400">{{ $activeFilterCount }}</span>
                @endif
            </button>
        </div>

        <div class="grid gap-8 lg:grid-cols-[15rem_minmax(0,1fr)]">
            {{-- Sol: filtre sütunu --}}
            <aside id="plp-filters" x-cloak
                   :class="mobileFiltersOpen ? 'block' : 'hidden lg:block'"
                   class="lg:sticky lg:top-24 lg:self-start">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="font-bold text-slate-900 dark:text-white">Filtreler</h2>
                        <button type="button"
                                wire:click="clearFilters"
                                class="text-xs font-semibold text-indigo-600 transition-colors hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                            Temizle
                        </button>
                    </div>

                    {{-- Arama --}}
                    <div class="mb-5">
                        <div class="relative">
                            <input id="plp-search"
                                   type="text"
                                   wire:model.live.debounce.300ms="searchTerm"
                                   placeholder="Ürün ara..."
                                   aria-label="Ürün ara"
                                   class="block w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 shadow-sm transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-indigo-400" />
                            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                    </div>

                    {{-- Marka — koleksiyon ürünlerindeki markalar; çoklu seçim (OR),
                         varyant gruplarıyla aynı başlangıçta kapalı accordion --}}
                    @if (!empty($brands))
                        <details class="group mb-4 rounded-lg border border-slate-200 dark:border-slate-700">
                            <summary class="flex cursor-pointer select-none items-center justify-between rounded-lg px-3 py-2.5 text-sm font-bold text-slate-900 transition-colors hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400 [&::-webkit-details-marker]:hidden">
                                Marka
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="space-y-2 border-t border-slate-200 px-3 py-3 dark:border-slate-700">
                                @foreach ($brands as $brand)
                                    <label for="plp-brand-{{ $brand['key'] }}" class="flex cursor-pointer items-center gap-2.5 rounded-lg px-1 py-0.5 text-sm text-slate-700 transition-colors hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">
                                        <input type="checkbox"
                                               id="plp-brand-{{ $brand['key'] }}"
                                               wire:model.live="brandFilters.{{ $brand['key'] }}"
                                               class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-500 dark:bg-slate-800" />
                                        <span>{{ $brand['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    {{-- Kategori --}}
                    @if (!empty($categories))
                        <div class="mb-5">
                            <label for="plp-category" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Kategori</label>
                            <select id="plp-category"
                                    wire:model="selectedCategoryId"
                                    class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition-colors focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-400">
                                <option value="">Tüm Kategoriler</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Etiketler — koleksiyon ürünlerindeki etiketler; çoklu seçim (OR),
                         varyant gruplarıyla aynı başlangıçta kapalı accordion --}}
                    @if (!empty($tags))
                        <details class="group mb-4 rounded-lg border border-slate-200 dark:border-slate-700">
                            <summary class="flex cursor-pointer select-none items-center justify-between rounded-lg px-3 py-2.5 text-sm font-bold text-slate-900 transition-colors hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400 [&::-webkit-details-marker]:hidden">
                                Etiketler
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="space-y-2 border-t border-slate-200 px-3 py-3 dark:border-slate-700">
                                @foreach ($tags as $tag)
                                    <label for="plp-tag-{{ $tag['key'] }}" class="flex cursor-pointer items-center gap-2.5 rounded-lg px-1 py-0.5 text-sm text-slate-700 transition-colors hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">
                                        <input type="checkbox"
                                               id="plp-tag-{{ $tag['key'] }}"
                                               wire:model.live="tagFilters.{{ $tag['key'] }}"
                                               class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-500 dark:bg-slate-800" />
                                        <span>{{ $tag['value'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endif

                    {{-- Varyant seçenekleri (Renk, Beden …) — aynı ada sahip option'lar tek grupta
                         birleşir; her grup başlangıçta kapalı açılır menüdür (accordion).
                         Koleksiyonda hiç değeri olmayan grup zaten listelenmez. --}}
                    @foreach ($options as $option)
                        <details class="group mb-4 rounded-lg border border-slate-200 dark:border-slate-700">
                            <summary class="flex cursor-pointer select-none items-center justify-between rounded-lg px-3 py-2.5 text-sm font-bold text-slate-900 transition-colors hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400 [&::-webkit-details-marker]:hidden">
                                {{ $option['name'] }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </summary>
                            <div class="space-y-2 border-t border-slate-200 px-3 py-3 dark:border-slate-700">
                                @foreach ($option['values'] as $value)
                                    <label for="plp-option-{{ $option['key'] }}-{{ $value['key'] }}"
                                           class="flex cursor-pointer items-center gap-2.5 rounded-lg px-1 py-0.5 text-sm text-slate-700 transition-colors hover:text-indigo-600 dark:text-slate-300 dark:hover:text-indigo-400">
                                        <input type="checkbox"
                                               id="plp-option-{{ $option['key'] }}-{{ $value['key'] }}"
                                               wire:model.live="optionFilters.{{ $option['key'] }}.{{ $value['key'] }}"
                                               class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-500 dark:bg-slate-800" />
                                        <span>{{ $value['label'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </details>
                    @endforeach

                    {{-- Filterable ürün öznitelikleri --}}
                    @if (!empty($attributeFilterOptions))
                        <div class="mb-5">
                            <p class="mb-1.5 text-sm font-medium text-slate-700 dark:text-slate-300">Öznitelikler</p>
                            @foreach ($attributeFilterOptions as $attributeOption)
                                <label for="plp-attr-{{ $attributeOption['handle'] }}" class="mb-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $attributeOption['name'] }}</label>
                                <select id="plp-attr-{{ $attributeOption['handle'] }}"
                                        wire:model.live="attributeFilters.{{ $attributeOption['handle'] }}"
                                        class="mb-3 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition-colors focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 last:mb-0 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-400">
                                    <option value="">Tümü</option>
                                    @foreach ($attributeOption['values'] as $attributeValue)
                                        <option value="{{ $attributeValue }}">{{ $attributeValue }}</option>
                                    @endforeach
                                </select>
                            @endforeach
                        </div>
                    @endif
                </div>
            </aside>

            {{-- Sağ: sonuçlar --}}
            <div class="min-w-0">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $collection['name'] }} ürünleri</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ count($products) }} ürün listeleniyor</p>
                    </div>
                    <div>
                        <label for="plp-sort" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Sıralama</label>
                        <select id="plp-sort"
                                wire:model.live="sortBy"
                                class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition-colors focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-400 sm:w-56">
                            <option value="default">Önerilen sıralama</option>
                            <option value="newest">En yeni</option>
                            <option value="price_asc">Fiyat: düşükten yükseğe</option>
                            <option value="price_desc">Fiyat: yüksekten düşüğe</option>
                        </select>
                    </div>
                </div>

                {{-- Aktif filtre rozetleri --}}
                @php
                    $selectedCategory = $selectedCategoryId ? collect($categories)->firstWhere('id', (int) $selectedCategoryId) : null;
                    $activeFilterChips = [];
                    foreach ($brandFilters as $chipBrandKey => $chipChecked) {
                        if ($chipChecked === false || $chipChecked === null || $chipChecked === '') {
                            continue;
                        }
                        $chipBrand = collect($brands)->firstWhere('key', (string) $chipBrandKey);
                        $activeFilterChips[] = [
                            'icon' => '✨',
                            'label' => $chipBrand['name'] ?? $chipBrandKey,
                            'path' => 'brandFilters.'.$chipBrandKey,
                            'clear' => false,
                        ];
                    }
                    foreach ($tagFilters as $chipTagKey => $chipChecked) {
                        if ($chipChecked === false || $chipChecked === null || $chipChecked === '') {
                            continue;
                        }
                        $chipTag = collect($tags)->firstWhere('key', (string) $chipTagKey);
                        $activeFilterChips[] = [
                            'icon' => '🔖',
                            'label' => $chipTag['value'] ?? $chipTagKey,
                            'path' => 'tagFilters.'.$chipTagKey,
                            'clear' => false,
                        ];
                    }
                    foreach ($optionFilters as $chipGroupKey => $chipValues) {
                        $chipGroup = collect($options)->firstWhere('key', (string) $chipGroupKey);
                        foreach ((array) $chipValues as $chipValueKey => $chipChecked) {
                            if ($chipChecked === false || $chipChecked === null || $chipChecked === '') {
                                continue;
                            }
                            $chipValue = collect($chipGroup['values'] ?? [])->firstWhere('key', (string) $chipValueKey);
                            $activeFilterChips[] = [
                                'icon' => '⚙️',
                                'label' => ($chipGroup['name'] ?? $chipGroupKey).': '.($chipValue['label'] ?? $chipValueKey),
                                'path' => 'optionFilters.'.$chipGroupKey.'.'.$chipValueKey,
                                'clear' => false,
                            ];
                        }
                    }
                    foreach ($attributeFilters as $chipHandle => $chipValue) {
                        if (! filled($chipValue)) {
                            continue;
                        }
                        $chipAttribute = collect($attributeFilterOptions)->firstWhere('handle', $chipHandle);
                        $activeFilterChips[] = [
                            'icon' => '🏷️',
                            'label' => ($chipAttribute['name'] ?? $chipHandle).': '.$chipValue,
                            'path' => 'attributeFilters.'.$chipHandle,
                            'clear' => null,
                        ];
                    }
                @endphp
                @if (filled($searchTerm) || $selectedCategory || $activeFilterChips)
                    <div class="mb-5 flex flex-wrap items-center gap-2">
                        @if (filled($searchTerm))
                            <button type="button" wire:click="$set('searchTerm', '')" class="chip-close inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-md transition-colors hover:bg-indigo-50 dark:border-indigo-500/40 dark:hover:bg-indigo-500/10">
                                🔍 «{{ $searchTerm }}»<span aria-hidden="true">✕</span>
                            </button>
                        @endif
                        @if ($selectedCategory)
                            <button type="button" wire:click="$set('selectedCategoryId', null)" class="chip-close inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-md transition-colors hover:bg-indigo-50 dark:border-indigo-500/40 dark:hover:bg-indigo-500/10">
                                🏷️ {{ $selectedCategory['name'] }}<span aria-hidden="true">✕</span>
                            </button>
                        @endif
                        @foreach ($activeFilterChips as $chip)
                            <button type="button" wire:click="$set('{{ $chip['path'] }}', {{ $chip['clear'] === false ? 'false' : 'null' }})" class="chip-close inline-flex items-center gap-1.5 rounded-full border border-indigo-200 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-md transition-colors hover:bg-indigo-50 dark:border-indigo-500/40 dark:hover:bg-indigo-500/10">
                                {{ $chip['icon'] }} {{ $chip['label'] }}<span aria-hidden="true">✕</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <x-cart-alert :message="$cartError" />

                @if (empty($products))
                    <x-empty-state icon="🔍"
                                   title="Bu filtrelerle ürün bulunamadı"
                                   description="Filtreleri gevşetmeyi deneyin.">
                        <x-button variant="outline" size="sm" wire:click="clearFilters">Filtreleri Temizle</x-button>
                    </x-empty-state>
                @else
                    @include('livewire.storefront.partials.product-grid', [
                        'gridProducts' => $products,
                        'wishlistIds' => $wishlistIds,
                        'gridClasses' => 'grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4',
                    ])
                @endif
            </div>
        </div>
    </div>
</div>
