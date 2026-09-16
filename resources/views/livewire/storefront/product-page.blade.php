<div>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <a href="{{ url('/') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-indigo-600 mb-8 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Alışverişe devam et
        </a>

        <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
            <div id="product-gallery">
                <div class="aspect-square overflow-hidden rounded-3xl bg-slate-100 shadow-sm">
                    @if ($product['image_url'])
                        <img id="gallery-main" src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" class="w-full h-full object-cover transition-opacity duration-200">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-300 text-8xl">📦</div>
                    @endif
                </div>

                @if (count($images) > 1)
                    <div class="mt-4 grid grid-cols-4 gap-3">
                        @foreach ($images as $index => $image)
                            @if ($image['url'])
                                <button type="button" class="gallery-thumb aspect-square overflow-hidden rounded-xl bg-slate-100 border border-slate-200 hover:border-indigo-300 transition-colors cursor-pointer {{ $index === 0 ? 'ring-2 ring-indigo-500' : '' }}" data-src="{{ $image['url'] }}" aria-label="Görsel {{ $index + 1 }}">
                                    <img src="{{ $image['url'] }}" alt="{{ $product['name'] }} görsel {{ $index + 1 }}" class="w-full h-full object-cover" loading="lazy">
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="pt-2">
                @if ($product['collection'] && $product['collection_url'])
                    <a href="{{ url('/koleksiyon/' . $product['collection_url']) }}" class="text-sm font-semibold uppercase tracking-widest text-indigo-600 hover:text-indigo-800">
                        {{ $product['collection'] }}
                    </a>
                @endif
                <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-slate-900">{{ $product['name'] }}</h1>

                @if ($reviewsCount > 0)
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <div class="flex items-center">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= (int) round($ratingAverage) ? 'text-amber-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm font-semibold text-slate-700">{{ number_format((float) $ratingAverage, 1, ',', '.') }}</span>
                        <button type="button" wire:click="selectTab('reviews')" class="text-sm text-indigo-600 hover:text-indigo-800 underline underline-offset-2">
                            {{ $reviewsCount }} değerlendirme
                        </button>
                    </div>
                @endif

                <div class="mt-5 flex items-baseline gap-3">
                    <div class="text-3xl font-bold text-slate-900">
                        {{ $product['price'] !== null ? number_format($product['price'] / 100, 2, ',', '.') . ' ₺' : '—' }}
                    </div>
                    @if ($product['price'] !== null)
                        <span class="text-sm text-slate-500">KDV dahil</span>
                    @endif
                </div>

                @if (! empty($product['description']))
                    <p class="mt-4 text-base leading-7 text-slate-600">{{ $product['description'] }}</p>
                @endif

                @if (! empty($features))
                    <div class="mt-6">
                        <h2 class="text-sm font-bold uppercase tracking-wider text-slate-900 mb-3">Öne Çıkan Özellikler</h2>
                        <ul class="space-y-2">
                            @foreach ($features as $feature)
                                <li class="flex items-start gap-2 text-sm text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                    <div class="flex items-center gap-2 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                        </svg>
                        Hızlı kargo
                    </div>
                    <div class="flex items-center gap-2 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        14 gün iade
                    </div>
                    <div class="flex items-center gap-2 rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-slate-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Güvenli ödeme
                    </div>
                </div>

                <div class="mt-8 border-t border-slate-200 pt-6">
                    @if (count($variants) > 1)
                        <div class="mb-5">
                            <label for="variant" class="mb-2 block text-sm font-medium text-slate-700">Varyant</label>
                            <select id="variant" wire:model.live="selectedVariantId" class="w-full max-w-sm rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($variants as $variant)
                                    <option value="{{ $variant['id'] }}">{{ $variant['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if ($product['stock'] !== null && $product['stock'] > 0)
                        <div class="mb-4 text-sm font-medium text-emerald-700">Stokta: {{ $product['stock'] }} adet</div>
                        <form wire:submit="addToCart" class="flex flex-wrap items-end gap-3">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-slate-700 mb-2">Adet</label>
                                <input id="quantity" type="number" min="1" max="{{ $product['stock'] }}" wire:model="quantity" class="w-24 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                Sepete Ekle
                            </button>
                        </form>
                        @error('quantity') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                        @if ($added)
                            <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                Ürün sepete eklendi. <a href="{{ url('/sepet') }}" class="font-semibold underline">Sepete git</a>
                            </div>
                        @endif
                    @else
                        <p class="rounded-lg bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">Bu ürün şu anda stokta yok.</p>
                    @endif
                    <div class="mt-3">
                        <button type="button" wire:click="toggle({{ $product['id'] }})"
                                class="inline-flex items-center justify-center gap-2 rounded-lg border px-6 py-3 font-semibold transition-colors
                                       {{ $this->inWishlist($product['id']) ? 'border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100' : 'border-slate-300 bg-white text-slate-700 hover:border-indigo-300 hover:text-indigo-600' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="{{ $this->inWishlist($product['id']) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 10-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            {{ $this->inWishlist($product['id']) ? 'İstek Listesinde' : 'İstek Listesine Ekle' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sekmeler --}}
        <div id="urun-sekmeleri" class="mt-16">
            <div class="border-b border-slate-200 flex gap-1 overflow-x-auto">
                <button type="button" wire:click="selectTab('description')" class="px-5 py-3 text-sm font-semibold whitespace-nowrap transition-colors {{ $activeTab === 'description' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-500 hover:text-slate-800' }}">
                    Ürün Açıklaması
                </button>
                <button type="button" wire:click="selectTab('reviews')" class="px-5 py-3 text-sm font-semibold whitespace-nowrap transition-colors {{ $activeTab === 'reviews' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-500 hover:text-slate-800' }}">
                    Yorumlar <span class="text-slate-400">({{ $reviewsCount }})</span>
                </button>
                <button type="button" wire:click="selectTab('returns')" class="px-5 py-3 text-sm font-semibold whitespace-nowrap transition-colors {{ $activeTab === 'returns' ? 'border-b-2 border-indigo-600 text-indigo-600' : 'text-slate-500 hover:text-slate-800' }}">
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
                            <p class="text-slate-500">Bu ürün için henüz detaylı açıklama girilmemiş.</p>
                        @else
                            <div class="space-y-4">
                                @foreach ($paragraphs as $paragraph)
                                    <p class="text-slate-600 leading-8">{{ $paragraph }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @elseif ($activeTab === 'reviews')
                    <div class="grid gap-10 lg:grid-cols-2 lg:items-start">
                        <div>
                            @if ($reviewsCount > 0)
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="text-5xl font-extrabold text-slate-900">{{ number_format((float) $ratingAverage, 1, ',', '.') }}</div>
                                    <div>
                                        <div class="flex items-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="h-5 w-5 {{ $i <= (int) round($ratingAverage) ? 'text-amber-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>
                                        <div class="text-sm text-slate-500 mt-1">{{ $reviewsCount }} değerlendirme</div>
                                    </div>
                                </div>
                                <ul class="space-y-4">
                                    @foreach ($reviews as $review)
                                        <li class="rounded-2xl border border-slate-200 bg-white p-5">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="flex items-center">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <svg class="h-4 w-4 {{ $i <= $review['rating'] ? 'text-amber-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    @endfor
                                                </div>
                                                <span class="text-xs text-slate-400">{{ $review['date'] }}</span>
                                            </div>
                                            @if ($review['title'])
                                                <h3 class="mt-2 font-semibold text-slate-900">{{ $review['title'] }}</h3>
                                            @endif
                                            <p class="mt-1 text-sm text-slate-600 leading-6">{{ $review['body'] }}</p>
                                            <p class="mt-3 text-xs font-medium text-slate-500">{{ $review['name'] }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                                    <div class="text-4xl mb-3">⭐</div>
                                    <h3 class="text-lg font-semibold text-slate-800">Bu ürün için henüz değerlendirme yok</h3>
                                    <p class="mt-1 text-sm text-slate-500">İlk yorumu siz yazın; onay sonrası burada listelenir.</p>
                                </div>
                            @endif
                        </div>
                        <div id="yorum-formu" class="rounded-2xl border border-slate-200 bg-white p-6">
                            <h3 class="text-lg font-bold text-slate-900">Değerlendirme Yaz</h3>
                            @if ($reviewSubmitted)
                                <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                    Değerlendirmeniz alındı. Onaylandıktan sonra bu sayfada listelenecek.
                                </div>
                            @endif
                            <form wire:submit="submitReview" class="mt-4 space-y-4">
                                <div>
                                    <label for="review-rating" class="mb-2 block text-sm font-medium text-slate-700">Puanınız</label>
                                    <select id="review-rating" wire:model="reviewRating" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="5">5 — Çok iyi</option>
                                        <option value="4">4 — İyi</option>
                                        <option value="3">3 — Orta</option>
                                        <option value="2">2 — Kötü</option>
                                        <option value="1">1 — Çok kötü</option>
                                    </select>
                                    @error('reviewRating') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="review-name" class="mb-2 block text-sm font-medium text-slate-700">Adınız</label>
                                    <input id="review-name" type="text" wire:model="reviewerName" maxlength="100" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Örn. Ayşe K.">
                                    @error('reviewerName') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="review-title" class="mb-2 block text-sm font-medium text-slate-700">Başlık <span class="text-slate-400">(isteğe bağlı)</span></label>
                                    <input id="review-title" type="text" wire:model="reviewTitle" maxlength="150" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Örn. Beklentiyi karşıladı">
                                    @error('reviewTitle') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="review-body" class="mb-2 block text-sm font-medium text-slate-700">Yorumunuz</label>
                                    <textarea id="review-body" wire:model="reviewBody" rows="4" maxlength="2000" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ürünü kullandınız mı? Deneyiminizi paylaşın..."></textarea>
                                    @error('reviewBody') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 font-semibold text-white shadow-sm hover:bg-indigo-700 transition-colors">
                                    Değerlendirmeyi Gönder
                                </button>
                                <p class="text-xs text-slate-400">Yorumlar yayınlanmadan önce moderasyon ekibimiz tarafından incelenir.</p>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="max-w-3xl space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-3">İptal ve İade Hakkı</h3>
                            <ul class="space-y-2">
                                <li class="flex items-start gap-2 text-sm text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>Ürünü teslim aldıktan sonra <strong>14 gün içinde</strong> hiçbir gerekçe göstermeden cayma hakkınızı kullanabilirsiniz.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>Kargoya verilmemiş siparişler için iptal talebinizi <strong>Hesabım → Siparişlerim</strong> sayfasından iletebilirsiniz.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>İade edilecek ürünün <strong>kutusu ve ambalajı hasarsız</strong>, tüm aksesuar ve faturasıyla birlikte olması gerekir.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>İade kargosu ücreti, ürün hatasından kaynaklanan iadelerde <strong>bize aittir</strong>; cayma hakkı iadelerinde alıcıya aittir.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>Hasarlı, yanlış veya eksik ürün teslimatlarında <strong>48 saat içinde</strong> bildirmeniz halinde ücretsiz değişim yapılır.</span>
                                </li>
                                <li class="flex items-start gap-2 text-sm text-slate-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    <span>İade onayından sonra ödemeniz <strong>1-3 iş günü</strong> içinde orijinal ödeme yönteminize iade edilir.</span>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 mb-3">İade Süreci Nasıl İşler?</h3>
                            <ol class="space-y-2 list-decimal list-inside text-sm text-slate-700">
                                <li>İade talebinizi Hesabım → Siparişlerim sayfasından oluşturun.</li>
                                <li>Size özel iade kodunu ürünle birlikte paketleyin.</li>
                                <li>Paketi anlaşmalı kargo firmasıyla gönderin.</li>
                                <li>Ürün depomuza ulaştıktan sonra kontrol edilir ve onaylanır.</li>
                                <li>Onay sonrası ödemeniz en geç 3 iş günü içinde iade edilir.</li>
                            </ol>
                        </div>
                        <p class="text-xs text-slate-400">Bu sayfadaki koşullar 6502 sayılı Tüketicinin Korunması Hakkında Kanun ve Mesafeli Sözleşmeler Yönetmeliği hükümlerine göre hazırlanmıştır.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

<script>
    (() => {
        const root = document.getElementById('product-gallery');
        if (!root || root.dataset.ready) return;
        root.dataset.ready = 'true';

        const main = root.querySelector('#gallery-main');
        const thumbs = root.querySelectorAll('.gallery-thumb');

        thumbs.forEach((thumb) => {
            thumb.addEventListener('click', () => {
                const src = thumb.dataset.src;
                if (!src || !main) return;
                if (main.getAttribute('src') !== src) {
                    main.style.opacity = '0';
                    setTimeout(() => {
                        main.setAttribute('src', src);
                        main.style.opacity = '1';
                    }, 150);
                }
                thumbs.forEach((t) => t.classList.remove('ring-2', 'ring-indigo-500'));
                thumb.classList.add('ring-2', 'ring-indigo-500');
            });
        });
    })();
</script>
