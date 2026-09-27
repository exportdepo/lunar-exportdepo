{{--
    ExportDepo footer bileşeni.

    Katmanlar (yukarıdan aşağıya):
    - Güven bandı: 4 hizmet rozeti (hızlı teslimat, güvenli ödeme, iade, destek).
    - Bülten bandı: e-posta abonelik formu (`livewire:storefront.newsletter-subscription`).
    - Ana grid: marka, kategoriler, hesabım (giriş durumuna duyarlı) ve
      sipariş takibi (Alpine ile `/siparis/{referans}` rotasına yönlendiren
      çalışan mini form — backend gerektirmez).
    - Alt bant: telif + ödeme yöntemi rozetleri. Stripe rozeti checkout ile
      aynı yapılandırma anahtarlarıyla denetlenir; yapılandırma yoksa yalnızca
      "Kapıda Ödeme" gösterilir (dürüst gösterim).

    Zemin her temada koyu (slate-900) tutulur; `dark:` varyantına gerek yoktur.
--}}
<footer class="mt-16 bg-slate-900 text-slate-300">
    {{-- Güven bandı --}}
    <div class="border-b border-slate-800">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 py-8 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-white">Hızlı Teslimat</p>
                    <p class="mt-0.5 text-xs text-slate-400">Tüm siparişlerde özenli kargo</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-white">Güvenli Ödeme</p>
                    <p class="mt-0.5 text-xs text-slate-400">Bilgileriniz SSL ile korunur</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a5 5 0 015 5v1M3 10l4-4M3 10l4 4" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-white">14 Gün İade</p>
                    <p class="mt-0.5 text-xs text-slate-400">Koşulsuz iade garantisi</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-800 text-indigo-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-white">7/24 Destek</p>
                    <p class="mt-0.5 text-xs text-slate-400">Sorularınızda yanınızdayız</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bülten bandı --}}
    <div class="border-b border-slate-800">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-4 px-4 py-8 sm:flex-row sm:items-center sm:px-6 lg:px-8">
            <div>
                <h3 class="text-lg font-bold text-white">Fırsatlardan ilk siz haberdar olun</h3>
                <p class="mt-1 text-sm text-slate-400">Yeni ürünler, indirimler ve kampanyalar e-postanızda.</p>
            </div>
            <livewire:storefront.newsletter-subscription />
        </div>
    </div>

    {{-- Ana link grid'i --}}
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-5">
            {{-- Marka --}}
            <div>
                <div class="mb-3 flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-linear-to-br from-indigo-500 to-purple-600 text-sm text-white">🌙</span>
                    <span class="font-semibold text-white">ExportDepo</span>
                </div>
                <p class="text-sm text-slate-400">Türkiye'nin yeni nesil e-ticaret deneyimi. Kaliteli ürünler, uygun fiyatlar ve güvenli alışveriş tek adreste.</p>
            </div>

            {{-- Kategoriler --}}
            <div>
                <h4 class="mb-3 text-sm font-semibold text-white">Kategoriler</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/koleksiyon/elektronik') }}" class="transition-colors hover:text-white">Elektronik</a></li>
                    <li><a href="{{ url('/koleksiyon/giyim') }}" class="transition-colors hover:text-white">Giyim</a></li>
                    <li><a href="{{ url('/koleksiyon/ev-yasam') }}" class="transition-colors hover:text-white">Ev &amp; Yaşam</a></li>
                </ul>
            </div>

            {{-- Kurumsal --}}
            <div>
                <h4 class="mb-3 text-sm font-semibold text-white">Kurumsal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/hakkimizda') }}" class="transition-colors hover:text-white">Hakkımızda</a></li>
                    <li><a href="{{ url('/iletisim') }}" class="transition-colors hover:text-white">İletişim</a></li>
                    <li><a href="{{ url('/gizlilik') }}" class="transition-colors hover:text-white">Gizlilik ve KVKK</a></li>
                    <li><a href="{{ url('/iade-degisim') }}" class="transition-colors hover:text-white">İade ve Değişim</a></li>
                    <li><a href="{{ url('/sss') }}" class="transition-colors hover:text-white">Sıkça Sorulan Sorular</a></li>
                </ul>
            </div>

            {{-- Hesabım: giriş durumuna göre içerik --}}
            <div>
                <h4 class="mb-3 text-sm font-semibold text-white">Hesabım</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/sepet') }}" class="transition-colors hover:text-white">Sepetim</a></li>
                    <li><a href="{{ url('/istek-listesi') }}" class="transition-colors hover:text-white">İstek Listem</a></li>
                    @auth
                        <li><a href="{{ url('/hesabim') }}" class="transition-colors hover:text-white">Hesabım</a></li>
                        <li><a href="{{ url('/hesabim/siparislerim') }}" class="transition-colors hover:text-white">Siparişlerim</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="transition-colors hover:text-white">Çıkış Yap</button>
                            </form>
                        </li>
                    @else
                        <li><a href="{{ url('/giris') }}" class="transition-colors hover:text-white">Giriş Yap</a></li>
                        <li><a href="{{ url('/kayit') }}" class="transition-colors hover:text-white">Üye Ol</a></li>
                    @endauth
                </ul>
            </div>

            {{-- Sipariş takibi: referans koduyla `/siparis/{referans}` rotasına gider --}}
            <div>
                <h4 class="mb-3 text-sm font-semibold text-white">Sipariş Takibi</h4>
                <p class="mb-3 text-xs text-slate-400">Sipariş durumunu referans kodunuzla sorgulayın.</p>
                <form x-data="{ reference: '' }"
                      @submit.prevent="if (reference.trim()) { window.location = '{{ url('/siparis') }}/' + encodeURIComponent(reference.trim()) }"
                      class="flex gap-2">
                    <label for="order-reference" class="sr-only">Sipariş referans kodu</label>
                    <input id="order-reference"
                           x-model="reference"
                           type="text"
                           placeholder="Referans kodu"
                           class="block h-10 w-full min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-800 px-3 text-sm text-white placeholder:text-slate-500 transition-colors focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30">
                    <x-button type="submit" size="sm" class="h-10 shrink-0">Sorgula</x-button>
                </form>
                <p class="mt-2 text-xs text-slate-500">Referans kodunuz sipariş onay e-postanızda bulunur.</p>
            </div>
        </div>

        {{-- Alt bant: telif + ödeme yöntemleri --}}
        <div class="mt-8 flex flex-col justify-between gap-3 border-t border-slate-800 pt-6 sm:flex-row sm:items-center">
            <span class="text-xs text-slate-500">© {{ date('Y') }} ExportDepo. Tüm hakları saklıdır.</span>
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-400">
                @php
                    // Checkout ile aynı anahtarlar: Stripe tam yapılandırılmadıysa
                    // rozet gösterilmez, yalnızca gerçekten kullanılabilen yöntem listelenir.
                    $stripeEnabled = filled(config('lunar.payments.types.stripe.secret'))
                        && filled(config('services.stripe.webhook_secret'));
                @endphp
                @if ($stripeEnabled)
                    <span class="inline-flex items-center gap-1.5 rounded-md border border-slate-700 px-2 py-1">💳 Kart ile Ödeme (Stripe)</span>
                @endif
                <span class="inline-flex items-center gap-1.5 rounded-md border border-slate-700 px-2 py-1">💵 Kapıda Ödeme</span>
                <span class="inline-flex items-center gap-1.5 rounded-md border border-slate-700 px-2 py-1">🇹🇷 Türkiye — TRY</span>
            </div>
        </div>
    </div>
</footer>

