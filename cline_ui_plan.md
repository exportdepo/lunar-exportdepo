# ExportDepo E-Ticaret UI Yenileme ve Geliştirme Rehberi

> **Durum:** Faz 1-12 + UI Cila Turu tamamlandı ve main'de · Sürümler: `v1.3.0` (Faz 1-6) + `v1.3.1` (Phase 7) + `v1.3.2` (Phase 8) + `v1.3.3` (Phase 9) + `v1.3.4` (Phase 10) + `v1.3.5` (Phase 11) + `v1.3.6` (sepet/sipariş düzeltmeleri) + `v1.4.0` (Phase 12)
> · Testler: 178 test / 1145 assertion yeşil · deprecation: 0 · Son güncelleme: 27.09.2026
> · Faz 13 (ürün kartı yenilemesi + slider) PLANLANDI — ayrıntı §6

Sevgili Cline, 
Bu belge, storefront arayüzü yenileme çalışmasının canlı yol haritasıdır: tamamlanan fazlar, kanıtlanmış çalışma kuralları ve sonraki fazlar burada takip edilir. Lütfen aşağıdaki kurallara ve fazlara sıkı sıkıya bağlı kalarak geliştirmeleri yap.

## 1. Proje Bağlamı ve Teknoloji Yığını
- **Backend:** Laravel 12, PHP 8.3, Lunar PHP (Headless E-commerce)
- **Frontend Mimarisi:** Blade + Livewire (tam sayfa bileşenleri) + Alpine.js — React/Vue KULLANILMIYOR
- **Stil Motoru:** Tailwind CSS v4 (Vite ile derleniyor; sınıf tabanlı dark mode)
- **Gösterim standartları:** Heroicons (inline SVG) ikonlar; fiyatlar ₺ simgesiyle; URL slug'ları ASCII (`/giris`, `/hesabim`), görünen metinler düzgün Türkçe
- **Kalite tabanı:** 178 test / 1145 assertion (güncel: `cline_ui_plan.md:4`) — her faz sonunda yeşil kalmalı

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
| Phase 13 | *bekliyor* | **Ürün kartı yenilemesi + slider (TÜM sayfalar)**: istek listesindeki elle yazılmış kart ortak `x-product-card`'a bağlandı (test kancaları `testid` prefix prop'u ile korundu); `compare_price` KDV dahil okuma (`StorefrontPricing::comparePriceForVariantInclTax`); marka rozeti + kupon bandı + eski fiyat/indirim bloğu; `config/storefront.php`; Alpine + CSS scroll-snap slider (`product-grid` `mode="grid|rail"`, `Alpine.data('productRail')`); Faz B = gerçek `lunar_discounts` kampanya altyapısı (ayrı onay). Detay: §6 |


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

## 5. Mevcut Envanter (27.09.2026 doğrulaması)

- **Bileşenler (10):** auth-card, button, cart-alert, cart-drawer, empty-state, header,
  input, product-card, textarea, layouts/app
- **Sayfalar (13 GET):** `/`, `/koleksiyon/{slug}`, `/urun/{slug}`, `/sepet`,
  `/istek-listesi`, `/checkout`, `/siparis/{reference}`, `/giris`, `/kayit`,
  `/sifremi-unuttum`, `/sifre-sifirlama/{token}`, `/hesabim`, `/hesabim/siparislerim`
  (ayrıca POST `/cikis`, POST `/stripe/webhook`)
- **Testler:** 36 feature test dosyası / 178 test / 1145 assertion — TODO-FIXME: 0
  (geçmişte `UNIQUE constraint failed: lunar_currencies.code` tek seferlik flake
  görüldü; tohumlama/paralellik kaynaklı, Phase 11 değişiklikleriyle ilgisiz)
- **Bilinen boşluklar:** `lang/` dizini yok (tüm metin view'larda gömülü — Phase 10'da tam migrasyon ERTELENDI, pilot secenegi: header/footer/auth);
  `emails/order-confirmation` şablonu tasarimli + koyu mod destekli (Phase 10); Phase 7 artıkları: gerçek cihazda
  manuel mobil QA, ölçümlü kontrast denetimi, modal/çekmecede focus-trap (ertelendi).
  Giderilenler (21.09.2026): `welcome.blade.php` silindi + uniqid() çakışması
  giderildi (Phase 6), checkout'a özel feature test eklendi (Phase 8)
- **Faz 13 öncesi bilinen tekrarlar:** `wishlist-page.blade.php:50-134` kendi ürün
  kartını elle yazıyordu (`x-product-card` kullanmıyor) — Phase 13 A1'de ortak
  bileşene bağlanacak
- **Faz 13 öncesi eksik altyapı:** `config/storefront.php` yok (A4'te oluşturulur);
  `lunar_discounts` tablosu mevcut ama projede hiç kullanılmıyor (Faz B);
  `compare_price` kolonu mevcut ama hep `null` ve hiç okunmuyor (A2'de bağlanır);
  vitrinde slider/carousel kütüphanesi yok — Alpine + CSS scroll-snap (A5)

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

### Phase 13: Ürün Kartı Yenilemesi ve Slider (PLANLANDI 27.09.2026)

**Kullanıcı kararı:** Kart görseldeki düzene göre yenilenecek ve **slider** olacak.
Kapsam: kart **TÜM sayfalarda** geçerli (vitrin + istek listesi). Önce **(A) UI katmanı**,
sonra **(B) gerçek Lunar kampanya altyapısı** (ayrı faz, ayrı onay).

**Yeni sohbete tek satır yeterli:**
`cline_ui_plan.md` dosyasındaki **Phase 13** bölümünü oku ve uygula.

#### A. UI Katmanı (bu faz)

**A1 — Ortak kartın tek kaynağa indirilmesi**
`resources/views/components/product-card.blade.php` içine iki prop eklenir:
- `testid` (string, varsayılan `''`) — `data-testid` prefix'i; istek listesinde
  `'wishlist-'` verilerek `wishlist-tags` / `wishlist-rating` kancaları korunur
- `removeFromList` (bool, varsayılan `false`) — true → sol üstte çöp kutusu
  (`wire:click="remove(...)"`), false → mevcut kalp (`wire:click="toggle(...)"`)

`resources/views/livewire/storefront/wishlist-page.blade.php` → satır **50-134**
arası elle yazılmış kart **silinir**, yerine `<x-product-card>` çağrılır.
- `wire:key="wishlist-item-{{ $item['id'] }}"` korunur (Livewire diff)
- Kart dışındaki `wire:key="wishlist-remove-..."` düğmesi de kaldırılır (bileşene taşınır)
- **Gerekçe:** İstek listesi bugün `x-product-card` **kullanmıyor**; 85 satırlık
  duplikasyon kaldırılırsa "tüm sayfalarda aynı kart" hedefi gerçekleşir.

**A2 — `compare_price` desteği (altyapı zaten mevcut)**
`app/Support/StorefrontPricing.php` → `comparePriceForVariantInclTax(ProductVariant): ?int`
eklenir; mevcut `resolve()` kalıbı, `MissingCurrencyPriceException` / `Throwable`
yakalaması **aynen korunur**. Kritik: `compare_price` Lunar'da **yalnızca `Price`
modelinde** vardır; `PricingResponse.matched` zaten `Lunar\Models\Price` tipindedir
→ `$matched->comparePriceIncTax($taxZone)` doğrudan çağrılabilir. **KDV dahil
okunur** (`forVariantInclTax` ile aynı sözleşme), `->value` değil.

Dört map noktasına alan düşer:
- `app/Livewire/Storefront/HomePage.php:45` (mount)
- `app/Livewire/Storefront/CollectionPage.php:428` (loadProducts)
- `app/Support/WishlistManager.php:162` (products)
- `app/Livewire/Storefront/ProductPage.php:113` (PDP — kart kullanmaz, dokunulmaz)

İndirim yüzdesi **kart içinde** hesaplanır: `round((compare - price) / compare * 100)`.
**A3 — Kartın görsele göre yeniden düzenlenmesi**
Sıra: 1) sol üst marka rozeti (amber/orange, yuvarlak) · 2) sağ üst kalp
(mevcut `left-2` → `right-2`) · 3) görsel (`aspect-square`) · 4) görsel altı
kupon bandı (amber/orange) · 5) başlık `line-clamp-2` · 6) yıldız + puan + adet
· 7) etiket rozetleri · 8) fiyat bloğu: eski fiyat `line-through` + yeşil `%30`
etiketi + büyük yeni fiyat · 9) sepete ekle (ikon-only).

Tüm yeni bloklar **`@if` koşullu** — veri yoksa render edilmez, mevcut düzen bozulmaz.
`data-testid` kancaları **korunur**.

Marka verisi: `Product::brand()` ilişkisi mevcut; seeder'da 3 marka var
(`DemoDataSeeder.php:502-517`). Eksik olan yalnızca `with()` + map'e eklemek.

**Renk kararı:** Kupon bandı ve marka rozeti amber/orange; fiyat vurgusu
emerald/indigo; geri kalan **slate/indigo** mevcut sistemde kalır (§2 md:19
kuralı: indigo primary, rose yalnızca istek listesi ve uyarıda).

**A4 — `config/storefront.php` (yeni dosya; şu an YOK)**
Kupon bandı / kampanya metinlerinin tek kaynağı. `cards.coupon_badge` ve
`cards.campaign_line` anahtarları `enabled` / `text` / `tone` taşır.
A fazında kart buradan okur; **B fazında okuma yeri yalnızca değişir** —
kademeli geçiş ve geriye dönük uyum sağlanır.

**A5 — Slider (Alpine + CSS scroll-snap, YENİ BAĞIMLILIK YOK)**
`resources/views/livewire/storefront/partials/product-grid.blade.php` →
`@props(['gridClasses' => ..., 'mode' => 'grid'])`; `grid` mevcut davranış,
`rail` yatay kaydırma + oklar.
`resources/js/app.js` → `Alpine.data('productRail', ...)`: `scrollBy(dir)`,
`scrollLeft` takibi, `atStart`/`atEnd` → disabled, `prefers-reduced-motion`
kontrolü. Mevcut `alpine:init` desenine eklenir (bkz. `app.js:10-15` ve `:29`/`:51`
store'ları) — **ikinci Alpine örneği başlatılmaz**.
`resources/css/app.css` → `.product-rail`: `scroll-snap-type: x mandatory`,
kart `snap-start`, gizlenmiş scrollbar, kenar fade'i.
`prefers-reduced-motion` emniyet ağı `app.css:70-79`'da **zaten var**.

Kullanım: `home-page.blade.php:77` (Öne Çıkan) ve `:133` (Tüm Ürünler) → `rail`;
`collection-page.blade.php:329` → `grid` kalır.

**A6 — Doğrulama zinciri (kural 4, değiştirilemez)**
`npm run build` → `php artisan view:clear` → `php artisan test`
(178 test / 1145 assertion yeşil) → ASCII-artifact grepi (`grid-cols=`,
`col-span=`, `chr(`, literal `icon="..."`, bozuk Türkçe kalıpları).

**A7 — Yeni test:** `tests/Feature/ProductCardTest.php` — kartın 4 yerde aynı
yapıyı render ettiği; `card-tags` / `card-rating` konum sırası korunuyor;
`wishlist-` prefix kancaları çalışıyor; `compare_price` varsa eski fiyat + %
etiketi, yoksa render edilmiyor; `mode="rail"` slider kabuğu + ok butonları,
`mode="grid"` ızgara sınıfları.

#### B. Gerçek Kampanya Altyapısı (ayrı faz, ayrı onay)

B fazında A'nın kart yapısı korunur; **yalnızca `config/storefront.php` okuma
yeri** değişir.

- **B1 —** `app/Support/StorefrontCampaigns.php` (yeni) — `StorefrontRatings`
  deseninde **N+1'siz** toplu okuma: tek sorguda aktif `lunar_discounts` +
  `lunar_discountables` pivot (`discountable_type = product`).
  `Discount::active()` scope'u; `AmountOff` için `data['percentage']` (yüzde)
  ve `data['fixed_values']` (sabit tutar). Çıktı:
  `['label' => 'Ek 50 TL Kupon', 'percent' => 10, 'coupon' => 'EK50TL', 'type' => ...]`
- **B2 —** Kartta gösterilen indirim ile **sepete uygulanan indirim aynı
  kaynaktan** gelmeli (`DiscountManager::getDiscounts()` ile aynı filtre).
  Aksi halde kart "30% indirim" der, sepette başka rakam çıkar — **yasal risk**.
- **B3 —** A2'deki üç map noktasına `campaign` alanı (tüm sayfalar kapsamı).
- **B4 —** Sepet kupon akışı: `Cart::coupon_code` hazır (`Cart.php:59`, cast `:212`),
  `DiscountManager::validateCoupon()` (`:274`). Kupon değişiminden sonra
  **`Discounts::resetDiscounts()`** çağrılmalı (Lunar SKILL.md: "Discounts are
  cached per request").
- **B5 —** Panel için **kod yazılmaz**: `DiscountResource` Lunar ile geliyor
  (`vendor/lunarphp/lunar/src/Filament/Resources/DiscountResource.php` +
  `ManageDiscountAvailability/Limitations` + `ProductConditionRelationManager`).
  Yalnızca `/lunar/discounts` listesinden kampanya oluşturma + ürüne bağlama
  doğrulanır. Erişim reddedilirse `AppServiceProvider.php:46-60` desenine bakılır.
- **B6 —** `DemoDataSeeder` → 2-3 gerçek `lunar_discounts` kaydı (yüzdesel +
  kuponlu), `lunar_discountables` pivot ile ürünlere bağlı, idempotent.
- **B7 —** A6 doğrulaması + `StorefrontCampaignsTest`: aktif/pasif filtresi,
  yüzde hesabının sepetteki gerçek indirimle **eşleşmesi**, kupon görünürlüğü,
  süresi dolmuş kampanya gösterilmiyor.

#### Phase 13 Kritik Uyarıları

1. **Test kancaları kırılabilir.** `tests/Feature/StorefrontTagsTest.php`
   konum sırası iddiaları içerir: etiket rozeti başlıktan SONRA (`:42-46`),
   puan satırı etiketlerinin ÜSTÜNDE (`:132-136`), aynısı wishlist varyantı
   için (`:106`, `:181-190`). Kart DOM'u yeniden düzenlenirken bu sıra korunur;
   `testid` prefix prop'u bu yüzden zorunludur.
2. **Kural 1 ihlali sessiz bozulma yaratır** — Türkçe karakterler (₺, ş, ğ, ı)
   ve SVG path'leri bozulur (Faz 5'te iki kez kanıtlandı). View metinleri
   **yalnızca editör aracıyla** yazılır.
3. **`compare_price` KDV dahil okunur** — `comparePriceIncTax()`; mevcut
   `forVariantInclTax` sözleşmesiyle tutarlı olmalı.
4. **N+1 tuzağı** — `StorefrontRatings.php:21` toplu sorgu deseni örnek alınır;
   kart başına sorgu yasaktır.
5. **Slider okları `overflow` tarafından kesilir** — mutlaka kaydırma sarmalayıcısının
   **dışında** konumlanır (aksi halde ilk kartın üstüne biner).
6. **Livewire nested component kullanılmaz** — `InteractsWithWishlist.php:10-14`
   bunu mimari gereklilik olarak belgeliyor; kart düğmeleri ana sayfa bileşenine
   bağlı kalır.
7. **A ve B ayrı commit'lerde** — B fazı kendi onayıyla başlar.


**A8 — Kayıt:** `CHANGELOG.md` `[Unreleased]` altına · bu tabloya Faz 13 satırı
· §5 envanter · commit mesajı **ASCII** (kural 2); bileşen kullanıma girer girmez
commit (kural 3).



`database/seeders/DemoDataSeeder.php:860` ve `:1372` → `'compare_price' => null`
yerine belirli ürünlerde gerçek oran (deterministik; `updateOrCreate` ve
idempotency deseni korunur).



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