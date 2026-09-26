# ExportDepo E-Ticaret UI Yenileme ve Geliştirme Rehberi

> **Durum:** Faz 1-12 + UI Cila Turu tamamlandı ve main'de · Sürümler: `v1.3.0` (Faz 1-6) + `v1.3.1` (Phase 7) + `v1.3.2` (Phase 8) + `v1.3.3` (Phase 9) + `v1.3.4` (Phase 10) + `v1.3.5` (Phase 11) + `v1.3.6` (sepet/sipariş düzeltmeleri) + `v1.4.0` (Phase 12)
> · Testler: 153 passed / 714 assertion · deprecation: 0 · Son güncelleme: 23.09.2026

Sevgili Cline, 
Bu belge, storefront arayüzü yenileme çalışmasının canlı yol haritasıdır: tamamlanan fazlar, kanıtlanmış çalışma kuralları ve sonraki fazlar burada takip edilir. Lütfen aşağıdaki kurallara ve fazlara sıkı sıkıya bağlı kalarak geliştirmeleri yap.

## 1. Proje Bağlamı ve Teknoloji Yığını
- **Backend:** Laravel 12, PHP 8.3, Lunar PHP (Headless E-commerce)
- **Frontend Mimarisi:** Blade + Livewire (tam sayfa bileşenleri) + Alpine.js — React/Vue KULLANILMIYOR
- **Stil Motoru:** Tailwind CSS v4 (Vite ile derleniyor; sınıf tabanlı dark mode)
- **Gösterim standartları:** Heroicons (inline SVG) ikonlar; fiyatlar ₺ simgesiyle; URL slug'ları ASCII (`/giris`, `/hesabim`), görünen metinler düzgün Türkçe
- **Kalite tabanı:** 103 test / 449 assertion — her faz sonunda yeşil kalmalı

## 2. Tasarım Prensipleri (Modern & Dönüşüm Odaklı)
- **Minimalizm:** Bol beyaz alan (whitespace) bırakılacak, içerik nefes alacak.
- **Tipografi:** Okunabilirliği yüksek, modern sans-serif fontlar kullanılacak.
- **Renk Paleti:** Nötr zemin `slate`; aksiyon butonlarında `indigo` (primary); `rose` yalnızca istek listesi ve uyarı vurgularında. Light/dark mode uyumlu sınıflar zorunludur.
- **Mobil Öncelikli (Mobile-First):** Tüm tasarımlar önce mobil cihazlar için yapılacak, ardından `sm:`, `md:`, `lg:` prefixleri ile masaüstüne genişletilecek.
- **Modülerlik:** Tekrar eden her arayüz elemanı, Laravel Anonymous Blade Component (`resources/views/components/`) olarak oluşturulacak.

## 3. Tamamlanan Fazlar (retrospektif)

| Faz | Commit | Kapsam |
|-----|--------|--------|
| 1 | `d1a43d1` | Vite + Tailwind v4 altyapısı, sınıf tabanlı dark mode, Alpine `theme` store, FOUC önleyici |
| 2 | `68bae7e` | Atomik bileşenler: `x-button` (7 varyant), `x-input`, `x-product-card` |
| 3 | `37b4456` + `13890ae` + `2328b27` | Sticky header, mobil menü, Türkçe duyarlı arama, Alpine sepet çekmecesi |
| 4a | `daebfd0` | Ana sayfa: hero + öne çıkan ürünler + arama yerleşimi |
| 4b | `a2b0aa3` + `e5c13ae` | PLP: sticky filtre kenar çubuğu + ürün kartı grid'i |
| 4c | `fc71d26` | PDP: Alpine galeri, varyant seçici, yorum formu bileşenleri |
| 5a-5c | `4bc7889` | auth/sepet/checkout view'larının atomik bileşenlere refaktoru; GEÇİCİ CSS kaldırıldı |
| 5d-5e | `77ac69c` | wishlist/hesap/siparişlerim sayfaları; `x-cart-alert` panel modu; Türkçe + ₺ + `wire:key`/`aria-label`/`wire:loading` onarımı |
| Hotfix | `5c7d5a8` | İstek listesi adet rozetindeki bozuk kalp SVG path'i onarıldı |
| UI Cila Turu | `8bc3d26`→`c508d36` | Kart grid yoğunlaşması (ana sayfa + istek listesi 5, PLP 4 sütun; ikon-only "Sepete Ekle"), istek listesi kart alt satırı + çöp butonu görsel köşesine, header yeniden düzeni (nav tam ortada, sağ ikon kümesi, gereksiz "Anasayfa" menü öğesi kaldırıldı), inline arama → ikon + ekran ortası modal popup, mobil menü md:hidden→lg:hidden düzeltmesi; hepsi editör aracıyla (kural 1) |
| Phase 6 | `bb8566d` + `eb5b484` + `e68daff` | Kalıntı temizliği: header/cart-indicator yorumlarındaki ASCII Türkçe + bayat arama yorumunun onarımı, kullanılmayan `welcome.blade.php` silindi, `LogoutTest` `#[Test]` attribute'larına taşındı (3 deprecation uyarısı bitti), `AddToCartFromListTest` uniqid() → `Str::ulid()` (çakışmaya dayanıklı üretim; 3x tekrar koşusu yeşil) |
| Phase 7 | `ec902da` + `2e6d80e` | Erişilebilirlik/QA: skip-link + `main` landmark (`id`/`tabindex`), global focus-visible emniyet ağı ve `prefers-reduced-motion` kuralı (app.css), sepet çekecesine `aria-labelledby`, 9 kart/bant yüzeyinde `dark:bg-slate-950`→`900` normalizasyonu, dekoratif katmanlara `aria-hidden`, wishlist "Tükendi" rozetine dark varyant; form etiketi/alt metin denetimi tam (PDP 5/5 + PLP 4/4 ilişkili) |
| Phase 8 | `075f286` + `0447d4b` + `cd64351` | Checkout derinleştirme: 8 testli `CheckoutTest` (offline mutlu yol: sipariş + adres + stok düşümü + onay maili + sepet temizliği; misafir/girişli `customer_id` bağlama; zorunlu alan, boş sepet, yapılandırılmamış Stripe, başarısız ödeme sürücüsü, yetersiz stok hataları), yetersiz stokta 500 yerine doğrulama hatası, adres formuna autocomplete nitelikleri |
| Phase 9 | `1c6f81a` + `a55e5a9` + `79b6944` | Performans ve SEO: 7 görsel konumunda width/height + decoding=async (CLS güvenliği), sayfa bazlı title/description (layoutData), Open Graph + Twitter meta, PDP Product JSON-LD (KDV dahil fiyat + stok durumu), /sitemap.xml + robots.txt + 2 test; build denetimi: CSS 77.86 kB / JS 52.11 kB (gzip 13/19.7) — kod bölme gereksiz kararı |
| Phase 10 | `22981eb` | Marka tutarliligi + e-posta koyu mod: 5 noktada Lunar Shop → ExportDepo (e-posta baslik/footer, login/register aciklamalari + test guncellemesi ve kalinti guvencesi), color-scheme meta + prefers-color-scheme dark medya sorgulari, dark-mode sema testi (7 test / 20 assertion). i18n: tam migrasyon ERTELENDI |
| Phase 11 | `3e72a28` + `d71d3a5` | Müşteri yönetimi + storefront cilası: panelde `CustomerResource` (List/Create/Edit/View sayfaları), FK kısıtından düşen müşteri silmesi için `DeleteCustomers` servisi (pivot + adres temizliği; sipariş geçmişi korunup `customer_id` null'a çekilir, sepetler silinmez), `CustomerDeleteTest` ile tekli/çoklu silme doğrulaması; sepet/ürün sayfa dokunuşları, `app.css` genişlemesi, istek listesi buton metni düzeltmesi |
| Phase 12 | `e6b8db3`→`c91bfe0` | Gelişmiş PLP filtreleri: aynı adlı option'ların tek grupta birleşmesi (grup içi OR, gruplar arası AND), çoklu seçim marka/etiket filtreleri, fiyat/yenilik sıralaması, filterable öznitelik filtresi, başlangıçta kapalı accordion grupları, okunabilir URL senkronu; ürün kartlarında etiket + değerlendirme özeti (`StorefrontRatings`); panelde etiket kolonu/filtresi (`ProductResource`); 3 markalı + etiketli idempotent demo verisi; storefront belirme animasyonları + Tailwind v4 gradient rename |

*Not: Faz 5 view'ları Python/PowerShell betikleriyle yazıldığı için Türkçe karakterler
ASCII'ye düşmüş, bazı Tailwind sınıfları ve bir SVG path'i bozulmuştu; 5d-5e ve hotfix
bunları temizledi. 20.09.2026 taraması: görünen metinlerde 0 bozuk Türkçe;
Phase 6 (21.09.2026) ile yorumlardaki son ASCII kalıntılar da onarıldı.*

| Sıcak Düzeltme | `f333e6a` | Header misafir üyelik menüsü: misafirde yan yana duran "Giriş Yap" + "Üye Ol" butonları kaldırıldı; yerine giriş yapmış menüyle aynı Alpine desenli (ikon + chevron, `open` durumu) tek kullanıcı ikonu açılır menüsü geldi. Panelde "Kayıt ol" başlığı altında `x-button` ile iki tam genişlik buton; `Esc`/dış tıklama ile kapanma, `aria-controls`/`aria-expanded`, `sm:` altı gizli. Mobil hamburger'de aynı başlık + buton hiyerarşisi. `HeaderGuestMenuTest` (2 test) + tam suite 178 test / 1145 assertion yeşil; editör aracıyla yazıldı (kural 1), Pint temiz |

## 4. Çalışma Kuralları (kanıtlanmış dersler)

1. **View/blade metin değişiklikleri yalnızca editör aracıyla yapılır.** Shell betikleri
   UTF-8 Türkçe karakterleri ve SVG arc parametrelerini bozdu (iki kez kanıtlandı).
2. **Commit mesajları ASCII kalır** (PowerShell→git argüman geçişinde ₺/emoji bozulabilir;
   Türkçe harfler güvenli).
3. **Bir bileşen kullanıma girdiği an commit edilir** (vaka: `auth-card` untracked kalmıştı;
   commit'lenmeseydi fresh checkout'ta login/register/reset sayfaları kırılırdı).
4. **Faz bitişi doğrulama zinciri:** `npm run build` → `php artisan view:clear` →
   `php artisan test` → ASCII-artifact grepi (`grid-cols=`, `col-span=`, `chr(`, literal
   `icon="..."` adları, bilinen bozuk Türkçe kalıpları).
5. **UI metin değişikliğinden önce** test `assertSee` beklentileri taranır.
6. **URL slug'ları ASCII kalır**; yalnızca görünen metinler düzgün Türkçe olur.
7. **Faz geçişi kullanıcı onayına bağlıdır**; ertelenen kararlar kayda geçer (ör. checkout
   `has-[:checked]` ödeme vurgusu — "şimdilik ekleme" kararı alındı).

## 5. Mevcut Envanter (21.09.2026 doğrulaması)

- **Bileşenler (10):** auth-card, button, cart-alert, cart-drawer, empty-state, header,
  input, product-card, textarea, layouts/app
- **Sayfalar (13 GET):** `/`, `/koleksiyon/{slug}`, `/urun/{slug}`, `/sepet`,
  `/istek-listesi`, `/checkout`, `/siparis/{reference}`, `/giris`, `/kayit`,
  `/sifremi-unuttum`, `/sifre-sifirlama/{token}`, `/hesabim`, `/hesabim/siparislerim`
  (ayrıca POST `/cikis`, POST `/stripe/webhook`)
- **Testler:** 29 feature test dosyası / 116 test / 519 assertion — TODO-FIXME: 0
  (22.09.2026 tam suite koşusunda `UNIQUE constraint failed: lunar_currencies.code`
  tek seferlik flake görüldü; ilgili test tek başına yeşil — tohumlama/paralellik kaynaklı,
  Phase 11 değişiklikleriyle ilgisiz)
- **Bilinen boşluklar:** `lang/` dizini yok (tüm metin view'larda gömülü — Phase 10'da tam migrasyon ERTELENDI, pilot secenegi: header/footer/auth);
  `emails/order-confirmation` şablonu tasarimli + koyu mod destekli (Phase 10); Phase 7 artıkları: gerçek cihazda
  manuel mobil QA, ölçümlü kontrast denetimi, modal/çekmecede focus-trap (ertelendi).
  Giderilenler (21.09.2026): `welcome.blade.php` silindi + uniqid() çakışması
  giderildi (Phase 6), checkout'a özel feature test eklendi (Phase 8)

## 6. Sonraki Fazlar (her faz başlamadan kullanıcı onayı alınır)

### Phase 10: Marka Tutarliligi ve E-posta (tamamlandi 22.09.2026)
- 5 noktada `Lunar Shop` → `ExportDepo`; e-postada color-scheme + dark medya sorgulari
- i18n tam migrasyonu ERTELENDI (maliyet: 28 view + ~30 test dosyasi); coklu dil ihtiyaci somutlasinca pilot (header/footer/auth) ile baslanir

### Phase 11: Müşteri Yönetimi ve Storefront Cilası (tamamlandi 22.09.2026)
- Panelde `CustomerResource`; varsayılan Filament silme akışı FK kısıtları
  (sipariş/sepet/adres/pivot, `on delete no action`) yüzünden düşüyordu →
  `DeleteCustomers` servisi silme öncesi pivot ve adresleri temizler,
  sipariş/sepet referanslarını null'a çeker (sipariş geçmişi korunur)
- `CustomerDeleteTest`: pivot + adres + sipariş + sepet içeren senaryoda
  tekli/çoklu silme doğrulaması
- Storefront cilası: cart/product sayfa dokunuşları, `app.css` genişlemesi,
  wishlist buton metni düzeltmesi (`İstek Listesine Ekle` → `İstek listesine ekle`)

### Phase 12: Gelişmiş Filtreler, Etiketler ve Değerlendirmeler (tamamlandı 23.09.2026)
- PLP filtre sistemi: aynı ada sahip option'lar (ürün başına yaratılan "Renk"/"Beden"
  tanımları) tek checkbox grubunda birleşir — grup içi OR, gruplar arası AND;
  marka ve etiket filtreleri çoklu seçimli; koleksiyonda eşleşmesi olmayan grup
  gizlenir; fiyat/yenilik sıralaması ve filterable öznitelik filtresi; tüm gruplar
  başlangıçta kapalı accordion; filtre durumu okunabilir URL parametreleriyle
  paylaşılır (`q`, `brand`, `tags`, `options`, `attrs`, `sort`)
- Vitrin genelinde ürün etiketleri + onaylı yorumlardan hesaplanan puan özeti
  (`StorefrontRatings`); panelde ürün listesine etiket kolonu ve filtresi
- Demo verisi: 3 markaya deterministik dağıtım + koleksiyon bazlı etiketler

## 7. Sürüm Kararları (kullanıcı onayına bağlı)
- `ui-refactor` 21.09.2026'da `origin/ui-refactor`'a push edildi (upstream takibi kuruldu)
- **Uygulandı (21-22.09.2026):** `v1.3.0` (Faz 1-6, `3ebb38f`) + `v1.3.1` (Phase 7,
  `fe84c44`) + `v1.3.2` (Phase 8, `9676915`) + `v1.3.3` (Phase 9, `d9da379`) +
  `v1.3.4` (Phase 10, `039e543`) annotated etiketleri — her sürüm
  Türkçe release notlarıyla GitHub Releases'te yayında (`v1.3.4` Latest)
- `v1.3.5` (Phase 11, `f80e071`) 22.09.2026'da eklendi: müşteri yönetimi
  (`CustomerResource` + `DeleteCustomers` servisi) ve storefront cilası
- `v1.3.6` (sepet/sipariş düzeltmeleri) ve `v1.4.0` (Phase 12, `c91bfe0`)
  23.09.2026'da eklendi: gelişmiş koleksiyon filtreleri, etiketler ve
  ürün değerlendirmeleri
- **Release yayını otomatikleşti (22.09.2026):** `v*` tag push'unda
  `.github/workflows/release.yml` çalışır; Türkçe notları CHANGELOG.md'deki
  sürüm bölümünden alır (yoksa tag mesajı, o da yoksa otomatik notlar) ve
  GitHub Release'i açar. Yeni sürüm akışı: CHANGELOG'a bölüm yaz →
  `git tag -a vX.Y.Z` → push (`main` + tag)
- **Stripe Elements değerlendirmesi (8c, 21.09.2026):** mevcut akış Stripe Checkout
  yönlendirmesi (`redirect()->away` + webhook) — PCI yükü sıfır, webhook zaten testli,
  hızlı; Payment Element embed marka uyumu sağlar ama Stripe.js entegrasyonu,
  3DS/SAQ-A-EP yükü ve yeni testler gerektirir (~1-2 faz). **Karar: yönlendirme
  akışı korunur;** embed Phase 9+ için opsiyonel
- Yedekler mevcut: `backup/pre-ui-refresh` dalı + `backup-pre-ui-phase1` etiketi