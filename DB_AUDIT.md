# ExportDepo — Veritabanı Denetim Raporu (DB_AUDIT)

- **Tarih:** 14.09.2026
- **Hedef:** `database/database.sqlite` (SQLite, 86 tablo, Laravel 12 + Lunar 1.5.0)
- **Yedek:** `database/database.sqlite.bak-20260914-225327.sqlite` (her işlem öncesi alındı, `.gitignore` kapsamında)

---

## 1. Denetim Kapsamı

- SQLite `integrity_check` + `foreign_key_check` (49 ilişki)
- Migration durumu (`php artisan migrate:status`) ve şema ↔ Lunar 1.5 uyumu
- Polymorphic eşleşmeler (`lunar_urls.element_*`, `lunar_channelables`, `media`, `lunar_cart_lines/order_lines.purchasable_*`, `activity_log.subject_*`/`causer_*`)
- Tüm FK ilişkilerinde orphan (yetim) taraması
- JSON kolon bütünlüğü (16 kolon, `json_valid`)
- Sepet/sipariş/kanal/para birimi/URL/fiyat/kullanıcı tutarlılıkları
- Kuyruk (`jobs`, `failed_jobs`) durumu

## 2. Bulgular (Düzeltme Öncesi)

### 🔴 Kritik
1. **Slug çakışmaları — 27 grup:** Aynı dilde aynı slug birden çok ürüne default (örn. `akilli-saat` → ürün #50, #65, #80). Aynı ürün için çift URL satırı (default=0 + default=1) mevcuttu. `/urun/{slug}` yanlış ürünü açabiliyordu.
2. **`activity_log` ~%76 orphan:** 1861 kayıttan 1411'i var olmayan konulara işaret ediyordu (order 320, product 228, product_variant 175, tax_class 146, product_type 112, customer 124, cart_line 99, cart 69, currency 49, collection_group 28, order_line 44, order_address 14, cart_address 3). Sebep: DB silinip yeniden seed edilmiş, log hiç temizlenmemiş.
3. **Sipariş satırı → varyant orphans (12):** order#161–199 satırları `variant_id 121–150`'ye işaret ediyor; maksimum varyant id 90 (eski seed kalıntısı).

### 🟠 Orta
4. **50/50 siparişte transaction yok, 49'unda adres yok** — manuel ödeme normali olabilir; test/demo kalıntısı ağırlıklı.
5. **30/50 sipariş satırsız** — `payment-offline`, `cart_id NULL`, ACC*/HTTP*/OTHER* referanslı; sepet akışı dışında üretilmiş.
6. **20/50 sipariş `placed_at NULL`** — 8 `payment-received` (çelişki), 8 `awaiting-payment`, 4 `draft`.
7. **20 sipariş `GBP`** — currencies tablosunda GBP yok (USD/TRY/KMF vardı); fiyatlar %100 TRY.
8. **47 ghost kanal** — faker insan-isimli, soft-delete'li; seeder dışı kaynak (manuel test/Filament). Canlı 2 kanal: `webstore` (default değil) + `web` (default).
9. **cart#4 ↔ order#23 uyumsuzluğu** — order sepeti işaret ediyor ama sepet `completed_at NULL` ve soft-delete'li.
10. **210 birikmiş kuyruk işi** — Scout `MakeSearchable` 128, `RemoveFromSearch` 77, `CreateCurrencyPrices` 3, `MarkAsNewCustomer` 2; worker hiç çalışmamış.

### 🟡 Bilgi
- Ghost para birimi: currency#52 `KMF` hiç kullanılmıyordu.
- 45 ürün = 15 demo ürünün 3 kopyası (seeder bug'ı — her çalıştırmada yeniden üretim).
- 105 markanın 60'ı ürünsüz (seeder dışı faker kayıtları); 104 marka URL'si farklı dillerde dağınık.
- 107 kullanıcı (id 1–157, 10 doğrulanmamış); email tekrarı yok.
- `scripts/` altındaki `_make_order.php` vb. scriptler satırsız test siparişlerinin muhtemel kaynağı.

### ✅ Temiz Görünen Alanlar
- SQLite dosya bütünlüğü **ok**, FK ihlali **0**, 146/146 migration **Ran** (Lunar 1.5 şeması uyumlu)
- JSON kolonlarının tamamı geçerli; polymorphic eşleşmeler (urls/channelables/media/cart_lines) sağlam
- 45 varyantın tümünün TRY fiyatı var; published ürünlerin tamamında default URL var
- Koleksiyon/müşteri grubu/tax/shipping pivot'ları tutarlı; rol atamaları doğru; email/referans/fingerprint tekrarı yok

## 3. Kök Neden — Seeder Bug'ı

`DemoDataSeeder::createProducts()` içindeki idempotency kontrolü:

```php
// HATALI — FQCN ile sorguluyor, DB'de morph alias ('product') saklanıyor:
Url::where('slug', $p['slug'])->where('element_type', Product::class)->first();
```

`Relation::enforceMorphMap` aktif olduğu için DB'de `element_type = 'product'` yazar; `Product::class` (`Lunar\Models\Product`) ile eşleşme hiçbir zaman bulunamıyor → her `db:seed` çalıştırmasında 15 ürün + varyant + fiyat + URL yeniden üretiliyordu (3 çalıştırma → 45 kopya, çakışan slug'lar).

## 4. Uygulanan Düzeltmeler

| Adım | İşlem | Sonuç |
|---|---|---|
| 1 | **Slug onarımı:** 45 çift URL satırı silindi; eleman başına tek default normalize edildi; dil bazlı benzersiz slug üretildi (ürün #61–75 → `-2`, #76–90 → `-3`) | 198 → **153 URL**, çakışma **0** |
| 2 | **Kalıntı sipariş temizliği:** satırsız + `placed_at NULL` + GBP siparişler ve satırları silindi | 49 sipariş, 19 order_line silindi; **1 meşru sipariş kaldı** (#23: TRY, placed, satırlı) |
| 3 | **cart#4 tamamlandı** (order#23'ün `placed_at` değeriyle geriye dönük) | order→cart uyumsuzluğu **0** |
| 4 | **Ghost kanallar:** referanssız 47 soft-delete kanal hard-delete | 49 → **2 kanal** (webstore + web[default]) |
| 5 | **KMF para birimi** silindi (0 referans) | 3 → **2 para birimi** (USD, TRY) |
| 6 | **activity_log orphan temizliği** (subject + causer) | 1583 kayıt silindi → **278** (tümü geçerli) |
| 7 | **Kuyruk:** 3 `CreateCurrencyPrices` job'ı silindi (0'lı fiyat üretecekti), 207 job `queue:work --stop-when-empty` ile işlendi | **jobs: 0, failed: 0** |
| 8 | **Seeder düzeltmesi:** exists-kontrolü `(new Product)->getMorphClass()` ile yapıldı; URL üretimi `firstOrCreate` + `uniqueProductSlug()` (dil bazlı benzersizlik, `-2/-3` sonek) ile korundu | Tekrar seed artık kopya/çakışma üretmez |

**Uygulama sonrası doğrulama:** `foreign_key_check` = 0, `integrity_check` = ok, kalan activity_log orphan = 0, slug çakışması = 0, çoklu default = 0, feature testleri **45 passed / 164 assertions**.

## 5. Kalan Takip Önerileri

1. **Ürün kopyaları:** 45 ürün hâlâ 15 demo ürünün 3 kopyası (`-2`/`-3` slug'lı). En temiz sonuç: 2. ve 3. kopyaları (ürün + varyant + fiyat + medya) silmek. İstenirse uygulanır.
2. **Ghost markalar:** 60 ürünsüz marka + 84 dilde dağınık 104 marka URL'si (seeder dışı faker kayıtları) — temizlenebilir.
3. **`scripts/` test scriptleri** (`_make_order.php` vb.) yeniden çalıştırılırsa yeniden test siparişi üretir — tek kullanımlık, istenirse silinebilir.
4. **Kanallar:** `webstore` kanalı canlı ama default değil ve hiç kullanılmıyor; tek kanal (`web`) hedefleniyorsa kaldırılabilir.
5. Kuyruğun yeniden birikmemesi için düzenli `queue:work --stop-when-empty` (ör. zamanlanmış görev) düşünülebilir.

---

# Ek — 2. Tur Temizlik (15.09.2026)

Takip önerilerinin tamamı uygulandı:

| Adım | İşlem | Sonuç |
|---|---|---|
| 1 | **Ürün kopyaları silindi:** 2. ve 3. kopyalar (#61–90) + varyant, fiyat, channelables, customer_group_product, URL ve 30 medya dosyası (diskten) | 45 → **15 ürün** (tek set demo) |
| 2 | **Ghost markalar silindi:** ürünsüz 104 marka + marka URL'leri + pivot'lar | 105 → **1 marka** (Lunar) |
| 3 | **webstore kanalı kaldırıldı:** 2 boş session sepetinin temizlenmesinin ardından kanal + channelables silindi (10 boş sepet de temizlendi) | 2 → **1 kanal** (`web`, default) |
| 4 | **`scripts/` test scriptleri silindi** (`_check.php`, `_make_order.php`, `test-order-mail.php`) | Tekrar test siparişi riski ortadan kalktı |
| 5 | **Final activity_log süpürmesi** | 183 orphan silindi → **100 kayıt** (tümü geçerli, orphan 0) |
| 6 | **Kuyruk önlemi:** `routes/console.php`'e her 5 dakikada `queue:work --stop-when-empty` schedule'ı eklendi (`withoutOverlapping` + `onOneServer`) | `schedule:list` doğrulandı |

## Final Durum

- **DB:** 15 ürün / 15 varyant / 18 fiyat / 18 medya / 1 marka / 1 kanal / 1 sipariş (2 satırlı, TRY) / 19 URL / 100 log
- **Bütünlük:** `integrity ok`, FK ihlali **0**, orphan **0**, slug çakışması **0**
- **Testler:** 45 passed / 164 assertions
- **Render doğrulaması:** `/` 200 · orijinal ürünler 200 · silinen kopya slug'ları (`-2`, `-3`) **404** · koleksiyonlar 200 · `/sepet`, `/checkout` 200
- **Yedek:** `database.sqlite.bak-20260915-011619.sqlite`

---

# Ek — 3. Tur: Dil Temizliği + Panel Toplu Silme (15.09.2026)

## Bulgu
`lunar_languages` tablosunda 74 kayıttan **72'si bozuk faker kayıtlarıydı** (`tr-6a9f18f352b71` biçimli kodlar + insan isimleri; panel/seeder dışı kötü kullanım). Kritik: **default dil işareti garbage kayıtta** (#84) duruyordu; gerçek Türkçe default değildi.

## Uygulanan Düzeltmeler
1. **Default dil düzeltildi:** garbage kayıttan alındı → gerçek **Türkçe (tr) default=1** (English ikinci dil).
2. **72 garbage dil silindi** — `lunar_urls` referansı olan olmadığından güvenli (URL'lerin tümü language_id=2'de). Sonuç: **2 dil kaldı (en, tr)**.
3. **Panel toplu silme eklendi:** `App\Filament\Resources\LanguageResource` (Lunar LanguageResource override) — seçilenleri sil aksiyonu; **default dil ve URL bağlantılı diller korunarak** (önce kontrol + uyarı).
4. **Yanlış dil tekrarını önleyen doğrulama:** kod alanı artık yalnızca geçerli ISO biçimi kabul eder (`tr`, `en`, `en-GB`) + benzersizlik (`ignoreRecord`).
5. `CleanLunarPanelManager::getResources()` override haritasına eklendi.

## Doğrulama
- `PRAGMA integrity_check` ok, FK ihlali 0, kalan dil: 2 (en, tr[default])
- Panel boot sağlıklı (`/exportdepo` → 302 login), LanguageResource override aktif
- Feature testleri: 45 passed / 164 assertions
- Yedek: `database.sqlite.bak-20260915-014039.sqlite`



