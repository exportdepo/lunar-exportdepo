# ExportDepo E-Ticaret UI Yenileme ve Geliştirme Rehberi

> **Durum:** Faz 1-6 + UI Cila Turu tamamlandı · Dal: `ui-refactor` (push edildi) · main'e tek seferde merge + `v1.3.0` etiketi: 21.09.2026 kararı (aynı oturumda uygulanır)
> · Testler: 103 passed / 449 assertion · deprecation: 0 · Son güncelleme: 21.09.2026

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
- **Testler:** 26 feature test dosyası / 103 test / 449 assertion — TODO-FIXME: 0
- **Bilinen boşluklar:** `lang/` dizini yok (tüm metin view'larda gömülü);
  checkout'a özel feature test yok; `emails/order-confirmation` şablonu tasarımsız
  (21.09.2026 itibarıyla `welcome.blade.php` silindi ve uniqid() çakışma riski
  `Str::ulid()` ile giderildi — Phase 6)

## 6. Sonraki Fazlar (her faz başlamadan kullanıcı onayı alınır)

### Phase 7: Erişilebilirlik ve QA Turu
- Klavye navigasyonu, `focus-visible` tutarlılığı, skip-link, `prefers-reduced-motion`
  (21.09 ön taraması: skip-link ve motion-reduce YOK; focus-visible yalnız 4 dosyada)
- `<main>` landmark'ına `id="main-content"` + `tabindex="-1"` (skip-link hedefi —
  `layouts/storefront.blade.php:16`)
- Form label/alt metin/kontrast denetimi; mobil QA (sepet çekmecesi, mobil menü, PLP filtre)
- Dark mode tam tur (`dark:bg-slate-900` ×17 / `dark:bg-slate-950` ×10 karışıklığının
  normalizasyonu — 21.09 sayımı)

### Phase 8: Checkout Derinleştirme
- `CheckoutTest` yazımı: `placeOrder` akışı, validasyon hataları, başarı ekranı, boş sepet
- Adres formu UX iyileştirmeleri; Stripe Elements görsel entegrasyon değerlendirmesi

### Phase 9: Performans ve SEO
- Build çıktı denetimi (CSS 89.75 kB / JS 52.11 kB; gzip ~16 / ~20 kB); gerekirse kod bölme
- Görsellerde `loading="lazy"` + `width/height` (CLS önleme) turu
- Meta açıklamaları, OG etiketleri, ürün JSON-LD, sitemap

### Phase 10 (opsiyonel): i18n ve E-posta
- Çoklu dil gerekirse gömülü metinlerin `lang/` + `__()` altyapısına taşınması
- `order-confirmation` e-postasının marka uyumlu, koyu mod dostu sade HTML'e alınması

## 7. Sürüm Kararları (kullanıcı onayına bağlı)
- `ui-refactor` 21.09.2026'da `origin/ui-refactor`'a push edildi (upstream takibi kuruldu)
- **Karar (21.09.2026):** Phase 6 sonrası `main`'e TEK SEFERDE merge + `v1.3.0`
  annotated etiketi (mevcut etiketler annotated, mesaj ASCII) — aynı oturumda
  uygulanır; Phase 7 çalışmaları `ui-refactor` üzerinde sürer
- Yedekler mevcut: `backup/pre-ui-refresh` dalı + `backup-pre-ui-phase1` etiketi