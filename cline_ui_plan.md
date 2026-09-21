# ExportDepo E-Ticaret UI Yenileme ve Geliştirme Rehberi

> **Durum:** Faz 1-8 + UI Cila Turu tamamlandı · Dal: `ui-refactor` (Phase 9 çalışma dalı) · Sürümler: `v1.3.0` (Faz 1-6) + `v1.3.1` (Phase 7) + `v1.3.2` (Phase 8 — aynı oturumda uygulanır)
> · Testler: 111 passed / 506 assertion · deprecation: 0 · Son güncelleme: 21.09.2026

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

*Not: Faz 5 view'ları Python/PowerShell betikleriyle yazıldığı için Türkçe karakterler
ASCII'ye düşmüş, bazı Tailwind sınıfları ve bir SVG path'i bozulmuştu; 5d-5e ve hotfix
bunları temizledi. 20.09.2026 taraması: görünen metinlerde 0 bozuk Türkçe;
Phase 6 (21.09.2026) ile yorumlardaki son ASCII kalıntılar da onarıldı.*

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
- **Testler:** 27 feature test dosyası / 111 test / 506 assertion — TODO-FIXME: 0
- **Bilinen boşluklar:** `lang/` dizini yok (tüm metin view'larda gömülü);
  `emails/order-confirmation` şablonu tasarımsız; Phase 7 artıkları: gerçek cihazda
  manuel mobil QA, ölçümlü kontrast denetimi, modal/çekmecede focus-trap (ertelendi).
  Giderilenler (21.09.2026): `welcome.blade.php` silindi + uniqid() çakışması
  giderildi (Phase 6), checkout'a özel feature test eklendi (Phase 8)

## 6. Sonraki Fazlar (her faz başlamadan kullanıcı onayı alınır)

### Phase 9: Performans ve SEO (sıradaki)
- Build çıktı denetimi (CSS 89.75 kB / JS 52.11 kB; gzip ~16 / ~20 kB); gerekirse kod bölme
- Görsellerde `loading="lazy"` + `width/height` (CLS önleme) turu
- Meta açıklamaları, OG etiketleri, ürün JSON-LD, sitemap

### Phase 10 (opsiyonel): i18n ve E-posta
- Çoklu dil gerekirse gömülü metinlerin `lang/` + `__()` altyapısına taşınması
- `order-confirmation` e-postasının marka uyumlu, koyu mod dostu sade HTML'e alınması

## 7. Sürüm Kararları (kullanıcı onayına bağlı)
- `ui-refactor` 21.09.2026'da `origin/ui-refactor`'a push edildi (upstream takibi kuruldu)
- **Uygulandı (21.09.2026):** `v1.3.0` (Faz 1-6, `3ebb38f`) + `v1.3.1` (Phase 7,
  `fe84c44`) annotated etiketleri; Phase 8 sonrası ÜÇÜNCÜ merge + `v1.3.2`
  annotated etiketi (aynı oturumda uygulanır)
- **Stripe Elements değerlendirmesi (8c, 21.09.2026):** mevcut akış Stripe Checkout
  yönlendirmesi (`redirect()->away` + webhook) — PCI yükü sıfır, webhook zaten testli,
  hızlı; Payment Element embed marka uyumu sağlar ama Stripe.js entegrasyonu,
  3DS/SAQ-A-EP yükü ve yeni testler gerektirir (~1-2 faz). **Karar: yönlendirme
  akışı korunur;** embed Phase 9+ için opsiyonel
- Yedekler mevcut: `backup/pre-ui-refresh` dalı + `backup-pre-ui-phase1` etiketi