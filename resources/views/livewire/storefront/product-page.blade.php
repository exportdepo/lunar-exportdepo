{{--
    Ürün detay sayfası (PDP) — Phase 4c.

    Akış: galeri (Alpine aktif görsel) + sağ panel (başlık, puan, fiyat, stok,
    varyant seçici, tek satırda adet + istek listesi kalbi + CTA) -> özellikler -> sekmeler.
    Tüm Livewire aksiyon imzaları korunur (selectOptionValue, selectTab,
    submitReview, addToCart, updatedSelectedVariantId, updatedQuantity).
--}}
<div>
    {{-- Yapılandırılmış veri: arama motorları için Product şeması --}}
    <script type="application/ld+json">{!! json_encode($this->getProductSchema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
        <nav aria-label="Ürün yolu" class="mb-6">
            <ol class="flex flex-wrap items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400">
                <li>
                    <a href="{{ url('/') }}" class="transition-colors hover:text-indigo-600 dark:hover:text-indigo-400">Anasayfa</a>
                </li>
                <li aria-hidden="true">/</li>
                @if ($product['collection'] && $product['collection_url'])
                    <li>
                        <a href="{{ url('/koleksiyon/' . $product['collection_url']) }}" class="transition-colors hover:text-indigo-600 dark:hover:text-indigo-400">{{ $product['collection'] }}</a>
                    </li>
                    <li aria-hidden="true">/</li>
                @endif
                <li class="max-w-full truncate font-medium text-slate-700 dark:text-slate-200">{{ $product['name'] }}</li>
            </ol>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
            {{-- Galeri --}}
            <div x-data="{ activeIndex: 0 }" id="product-gallery">
                <div class="relative aspect-square overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 shadow-sm dark:border-slate-800 dark:bg-slate-800">
                    @foreach ($images as $index => $image)
                        <img src="{{ $image['url'] ?? '' }}"
                             alt="{{ $product['name'] }} görsel {{ $index + 1 }}"
                             loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                             decoding="async"
                             width="1200"
                             height="1200"
                             x-cloak
                             x-show="activeIndex === {{ $index }}"
                             x-transition.opacity.duration.300ms
                             class="absolute inset-0 h-full w-full object-cover">
                    @endforeach
                    @if (empty($images) || empty($images[0]['url']))
                        <div class="absolute inset-0 flex items-center justify-center text-8xl text-slate-300 dark:text-slate-600" aria-hidden="true">📦</div>
                    @endif

                    @if ($product['stock'] !== null && $product['stock'] <= 0)
                        <span class="absolute left-4 top-4 rounded-full bg-rose-600 px-3 py-1.5 text-sm font-bold text-white shadow-lg">Tükendi</span>
                    @elseif ($product['stock'] !== null && $product['stock'] <= 5 && $product['stock'] > 0)
                        <span class="absolute left-4 top-4 rounded-full bg-amber-500 px-3 py-1.5 text-sm font-bold text-white shadow-lg">Son {{ $product['stock'] }} ürün</span>
                    @endif
                </div>

                @if (count($images) > 1)
                    <div class="mt-4 grid grid-cols-5 gap-3" role="tablist" aria-label="Ürün görselleri">
                        @foreach ($images as $index => $image)
                            @if ($image['url'])
                                <button type="button"
                                        @click="activeIndex = {{ $index }}"
                                        :aria-selected="activeIndex === {{ $index }}"
                                        :class="activeIndex === {{ $index }} ? 'border-indigo-600 ring-2 ring-indigo-500/40' : 'border-slate-200 hover:border-indigo-300 dark:border-slate-700 dark:hover:border-indigo-500'"
                                        class="aspect-square overflow-hidden rounded-xl border-2 bg-slate-100 transition-all dark:bg-slate-800"
                                        role="tab"
                                        aria-label="Görsel {{ $index + 1 }}">
                                    <img src="{{ $image['url'] }}"
                                         alt="{{ $product['name'] }} görsel {{ $index + 1 }}"
                                         loading="lazy"
                                         decoding="async"
                                         width="200"
                                         height="200"
                                         class="h-full w-full object-cover">
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Sağ panel --}}
            <div class="pt-1">
                @if ($product['collection'] && $product['collection_url'])
                    <a href="{{ url('/koleksiyon/' . $product['collection_url']) }}"
                       class="text-sm font-semibold uppercase tracking-widest text-indigo-600 transition-colors hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                        {{ $product['collection'] }}
                    </a>
                @endif

                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">{{ $product['name'] }}</h1>

                @if ($reviewsCount > 0)
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <div class="flex items-center" aria-hidden="true">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= (int) round($ratingAverage) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ number_format((float) $ratingAverage, 1, ',', '.') }}</span>
                        <button type="button" wire:click="selectTab('reviews')" class="text-sm text-indigo-600 transition-colors hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">({{ $reviewsCount }} değerlendirme)</button>
                    </div>
                @endif

                <div class="mt-4 flex items-end gap-2">
                    @if ($product['price'] !== null)
                        <span class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ number_format($product['price'] / 100, 2, ',', '.') }} ₺</span>
                        <span class="pb-1 text-sm text-slate-500 dark:text-slate-400">KDV dahil</span>
                    @else
                        <span class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">—</span>
                    @endif
                </div>

                @if (! empty($product['description']))
                    <p class="mt-4 line-clamp-3 text-base leading-7 text-slate-600 dark:text-slate-400">{{ $product['description'] }}</p>
                @endif

                @if (! empty($features))
                    <div class="mt-6">
                        <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-900 dark:text-white">Öne Çıkan Özellikler</h2>
                        <ul class="space-y-2">
                            @foreach ($features as $feature)
                                <li class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-1 gap-2 text-xs sm:grid-cols-3">
                    <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                        Hızlı kargo
                    </div>
                    <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        14 gün iade
                    </div>
                    <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Güvenli ödeme
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6 dark:border-slate-800">
                    @if (count($optionGroups) > 0)
                        <fieldset class="mb-6 space-y-5">
                            <legend class="sr-only">Ürün seçenekleri</legend>
                            @foreach ($optionGroups as $group)
                                <div>
                                    <p class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">
                                        {{ $group['name'] }}
                                        @foreach ($group['values'] as $value)
                                            @if ($this->isValueSelected($group['id'], $value['id']))
                                                <span class="font-normal text-slate-400 dark:text-slate-500">: {{ $value['name'] }}</span>
                                            @endif
                                        @endforeach
                                    </p>

                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($group['values'] as $value)
                                            @php
                                                $selected = $this->isValueSelected($group['id'], $value['id']);
                                                $available = $this->isValueAvailable($group['id'], $value['id']);
                                            @endphp

                                            @if ($group['is_colour'])
                                                <button type="button"
                                                        wire:click="selectOptionValue({{ $group['id'] }}, {{ $value['id'] }})"
                                                        @disabled(! $available)
                                                        title="{{ $value['name'] }}{{ $available ? '' : ' (stokta yok)' }}"
                                                        aria-label="{{ $value['name'] }}"
                                                        aria-pressed="{{ $selected ? 'true' : 'false' }}"
                                                        class="flex h-10 w-10 items-center justify-center rounded-full border-2 transition-all
                                                               {{ $selected ? 'border-indigo-600 ring-2 ring-indigo-500/40' : 'border-slate-200 hover:border-indigo-400 dark:border-slate-600' }}
                                                               {{ $available ? '' : 'cursor-not-allowed opacity-40' }}">
                                                    <span class="h-7 w-7 rounded-full border border-slate-300 dark:border-slate-600" style="background-color: {{ $value['hex'] ?: '#e2e8f0' }}" aria-hidden="true"></span>
                                                </button>
                                            @else
                                                <button type="button"
                                                        wire:click="selectOptionValue({{ $group['id'] }}, {{ $value['id'] }})"
                                                        @disabled(! $available)
                                                        title="{{ $value['name'] }}{{ $available ? '' : ' (stokta yok)' }}"
                                                        aria-pressed="{{ $selected ? 'true' : 'false' }}"
                                                        class="min-w-[3rem] rounded-lg border px-4 py-2 text-sm font-medium transition-colors
                                                               {{ $selected ? 'border-indigo-600 bg-indigo-50 text-indigo-700 dark:border-indigo-400 dark:bg-indigo-500/15 dark:text-indigo-300' : 'border-slate-300 bg-white text-slate-700 hover:border-indigo-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200' }}
                                                               {{ $available ? '' : 'cursor-not-allowed border-slate-200 text-slate-300 line-through dark:border-slate-700 dark:text-slate-600' }}">
                                                    {{ $value['name'] }}
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </fieldset>
                    @elseif (count($variants) > 1)
                        <div class="mb-5">
                            <label for="variant" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Varyant</label>
                            <select id="variant"
                                    wire:model.live="selectedVariantId"
                                    class="block w-full max-w-sm rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition-colors focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-400">
                                @foreach ($variants as $variant)
                                    <option value="{{ $variant['id'] }}">{{ $variant['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($product['stock'] !== null && $product['stock'] > 0)
                        <p class="mb-4 flex items-center gap-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-400">
                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500" aria-hidden="true"></span>
                            Stokta: {{ $product['stock'] }} adet
                        </p>
                    @endif

                    @php $inWishlist = $this->inWishlist($product['id']); @endphp

                    @if (($product['stock'] ?? 0) > 0)
                        <form wire:submit="addToCart" class="flex flex-wrap items-stretch gap-3">
                            <div x-data="{ qty: @entangle('quantity') }" class="flex items-stretch overflow-hidden rounded-lg border border-slate-300 transition-colors focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/30 dark:border-slate-600 dark:focus-within:border-indigo-400 dark:focus-within:ring-indigo-400/30">
                                <button type="button"
                                        @click="qty = Math.max(1, (parseInt(qty) || 1) - 1)"
                                        :disabled="(parseInt(qty) || 1) <= 1"
                                        aria-label="Adedi azalt"
                                        class="px-3 text-slate-600 transition-colors hover:bg-slate-100 disabled:opacity-40 dark:text-slate-300 dark:hover:bg-slate-800">−</button>
                                <input id="quantity"
                                       x-model.number="qty"
                                       type="number"
                                       min="1"
                                       @if ($product['stock'] !== null) max="{{ $product['stock'] }}" @endif
                                       aria-label="Adet"
                                       class="qty-input w-14 py-3 text-center text-sm font-semibold text-slate-900 dark:bg-slate-800 dark:text-slate-100" />
                                <button type="button"
                                        @click="qty = Math.min({{ $product['stock'] ?? 999 }}, (parseInt(qty) || 1) + 1)"
                                        aria-label="Adedi artır"
                                        class="px-3 text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">+</button>
                            </div>

                            {{-- İkon-only istek listesi düğmesi: etiket yerine title/aria-label, --}}
                            {{-- durum için aria-pressed (product-card kalp butonu deseni). --}}
                            <button type="button"
                                    wire:click="toggle({{ $product['id'] }})"
                                    wire:key="wishlist-pdp-{{ $product['id'] }}"
                                    title="{{ $inWishlist ? 'İstek listesinden çıkar' : 'İstek listesine ekle' }}"
                                    aria-label="{{ $inWishlist ? 'İstek listesinden çıkar' : 'İstek listesine ekle' }}"
                                    aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
                                    class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border shadow-sm transition-colors
                                           {{ $inWishlist
                                              ? 'border-rose-200 bg-rose-50 text-rose-600 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-400'
                                              : 'border-slate-300 bg-white text-slate-500 hover:border-rose-200 hover:text-rose-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-rose-400' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>

                            <x-button type="submit" size="lg" wire:loading.attr="disabled">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Sepete Ekle
                            </x-button>
                        </form>

                        @error('quantity')
                            <p class="mt-2 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                        @enderror

                        @if ($added)
                            <div class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                                ✅ Ürün sepete eklendi. <a href="{{ url('/sepet') }}" class="font-semibold underline underline-offset-2">Sepete git</a>
                            </div>
                        @endif
                    @else
                        <p class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">Bu ürün şu anda stokta yok.</p>

                        <div class="mt-3">
                            <button type="button"
                                    wire:click="toggle({{ $product['id'] }})"
                                    wire:key="wishlist-pdp-{{ $product['id'] }}"
                                    title="{{ $inWishlist ? 'İstek listesinden çıkar' : 'İstek listesine ekle' }}"
                                    aria-label="{{ $inWishlist ? 'İstek listesinden çıkar' : 'İstek listesine ekle' }}"
                                    aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
                                    class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-lg border shadow-sm transition-colors
                                           {{ $inWishlist
                                              ? 'border-rose-200 bg-rose-50 text-rose-600 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-400'
                                              : 'border-slate-300 bg-white text-slate-500 hover:border-rose-200 hover:text-rose-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-400 dark:hover:text-rose-400' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="{{ $inWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Sekmeler --}}
        <div id="urun-sekmeleri" class="mt-16">
            <div class="flex gap-1 overflow-x-auto border-b border-slate-200 dark:border-slate-800" role="tablist" aria-label="Ürün bilgileri">
                <button type="button"
                        wire:click="selectTab('description')"
                        role="tab"
                        :aria-selected="$wire.activeTab === 'description' ? 'true' : 'false'"
                        class="whitespace-nowrap px-5 py-3 text-sm font-semibold transition-colors {{ $activeTab === 'description' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                    Ürün Açıklaması
                </button>
                <button type="button"
                        wire:click="selectTab('reviews')"
                        role="tab"
                        class="whitespace-nowrap px-5 py-3 text-sm font-semibold transition-colors {{ $activeTab === 'reviews' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                    Yorumlar <span class="text-slate-400">({{ $reviewsCount }})</span>
                </button>
                <button type="button"
                        wire:click="selectTab('returns')"
                        role="tab"
                        class="whitespace-nowrap px-5 py-3 text-sm font-semibold transition-colors {{ $activeTab === 'returns' ? 'border-b-2 border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200' }}">
                    İptal ve İade Koşulları
                </button>
            </div>

            <div class="py-8">
                @if ($activeTab === 'description')
                    <div class="max-w-3xl">
                        @php
                            $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $product['description']))));
                        @endphp
                        @if (empty($paragraphs))
                            <p class="text-slate-500 dark:text-slate-400">Bu ürün için henüz detaylı açıklama girilmemiş.</p>
                        @else
                            <div class="space-y-4">
                                @foreach ($paragraphs as $paragraph)
                                    <p class="leading-8 text-slate-600 dark:text-slate-400">{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif ($activeTab === 'reviews')
                    <div class="grid items-start gap-10 lg:grid-cols-2">
                        <div>
                            @if ($reviewsCount > 0)
                                <div class="mb-6 flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                                    <span class="text-4xl font-extrabold text-slate-900 dark:text-white">{{ number_format((float) $ratingAverage, 1, ',', '.') }}</span>
                                    <div>
                                        <div class="flex items-center" aria-hidden="true">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="h-4 w-4 {{ $i <= (int) round($ratingAverage) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $reviewsCount }} değerlendirme</div>
                                    </div>
                                </div>
                                <ul class="space-y-4">
                                    @foreach ($reviews as $review)
                                        <li class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center" aria-hidden="true">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <svg class="h-4 w-4 {{ $i <= $review['rating'] ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    @endfor
                                                </div>
                                                <span class="text-xs text-slate-400 dark:text-slate-500">{{ $review['date'] }}</span>
                                            </div>
                                            @if ($review['title'])
                                                <h3 class="mt-2 font-semibold text-slate-900 dark:text-white">{{ $review['title'] }}</h3>
                                            @endif
                                            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $review['body'] }}</p>
                                            <p class="mt-3 text-xs font-medium text-slate-500">{{ $review['name'] }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <x-empty-state icon="⭐"
                                               title="Bu ürün için henüz değerlendirme yok"
                                               description="İlk yorumu siz yazın; onay sonrası burada listelenir." />
                            @endif
                        </div>

                        <div id="yorum-formu" class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Değerlendirme Yaz</h3>
                            @if ($reviewSubmitted)
                                <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                                    Değerlendirmeniz alındı. Onaylandıktan sonra bu sayfada listelenecek.
                                </div>
                            @endif
                            <form wire:submit="submitReview" class="mt-4 space-y-4">
                                <div>
                                    <label for="review-rating" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Puanınız</label>
                                    <select id="review-rating"
                                            wire:model="reviewRating"
                                            class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition-colors focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-400">
                                        <option value="5">5 — Çok iyi</option>
                                        <option value="4">4 — İyi</option>
                                        <option value="3">3 — Orta</option>
                                        <option value="2">2 — Kötü</option>
                                        <option value="1">1 — Çok kötü</option>
                                    </select>
                                    @error('reviewRating')
                                        <p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="review-name" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Adınız</label>
                                    <x-input name="review-name" type="text" wire:model="reviewerName" maxlength="100" placeholder="Örn. Ayşe K." class="" />
                                    @error('reviewerName')
                                        <p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="review-title" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Başlık <span class="font-normal text-slate-400">(isteğe bağlı)</span></label>
                                    <x-input name="review-title" type="text" wire:model="reviewTitle" maxlength="150" placeholder="Örn. Beklentiyi karşıladı" class="" />
                                    @error('reviewTitle')
                                        <p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="review-body" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Yorumunuz</label>
                                    <x-textarea name="review-body" wire:model="reviewBody" rows="4" maxlength="2000" placeholder="Ürünü kullandınız mı? Deneyiminizi paylaşın..." />
                                    @error('reviewBody')
                                        <p class="mt-1 text-sm font-medium text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <x-button type="submit" block wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="submitReview">Değerlendirmeyi Gönder</span>
                                    <span wire:loading wire:target="submitReview">Gönderiliyor...</span>
                                </x-button>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Yorumlar yayınlanmadan önce moderasyon ekibimiz tarafından incelenir.</p>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="max-w-3xl space-y-6">
                        <div>
                            <h3 class="mb-3 text-lg font-bold text-slate-900 dark:text-white">İptal ve İade Hakkı</h3>
                            <ul class="space-y-2">
                                <li class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>Ürünü teslim aldıktan sonra <strong>14 gün içinde</strong> hiçbir gerekçe göstermeden cayma hakkınızı kullanabilirsiniz.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>Kargoya verilmemiş siparişler için iptal talebinizi <strong>Hesabım → Siparişlerim</strong> sayfasından iletebilirsiniz.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>İade edilen ürünlerin bedeli, onay sonrası <strong>aynı ödeme yöntemiyle</strong> iade edilir.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-700 dark:text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>Hijyen ve kişiselleştirilmiş ürünlerde cayma hakkı sınırlıdır; ürün sayfasındaki açıklamayı inceleyin.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
