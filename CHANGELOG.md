# Değişiklik Geçmişi

Önemli değişiklikler bu dosyada Türkçe olarak tutulur; sürümler yeniden eskiye sıralanır. Yayın tarihleri GitHub Releases kayıtlarına göre UTC tarihidir.

## [Unreleased]

### Yenilikler

* **Header misafir üyelik menüsü** — Misafir ziyaretçide header'da yan yana duran "Giriş Yap" ve "Üye Ol" butonları kaldırıldı; yerine giriş yapmış kullanıcı menüsüyle aynı Alpine desenini kullanan tek bir kullanıcı ikonu açılır menüsü geldi. Panelin **"Kayıt ol"** başlığı altında "Üye Ol" (primary) ve "Giriş Yap" (outline) butonları tam genişlikte toplanır. Menü `Esc` ve dışarı tıklama ile kapanır, `aria-controls`/`aria-expanded` ile duyurulur, `sm:` altında gizlenir (mobilde hamburger devrede). Mobil hamburger menüsünde de aynı başlık + buton hiyerarşisi uygulandı.
* **Kurumsal sayfalar** — Hakkımızda, İletişim, Gizlilik (KVKK), İade & Değişim ve Sıkça Sorulan Sorular sayfaları eklendi; `/hakkimizda`, `/iletisim`, `/gizlilik`, `/iade-degisim`, `/sss` rotalarıyla sunulur. SSS sayfasında sorular Alpine accordion olarak açılıp kapanır (`aria-expanded` senkron). Tümü mobil öncelikli, koyu mod uyumlu ve atlama bağlantısına uyar.
* **İletişim formu** — Ad, e-posta, konu ve mesaj alanlarıyla çalışan form; sunucu taraflı doğrulama, **honeypot** (bot koruması) ve 5 denemede bir **rate limit** içerir. Başarılı gönderimde form temizlenir ve onay gösterilir. Mesajlar `contact_messages` tablosuna `ip_address` ve `is_read` ile kaydedilir.
* **Bülten aboneliği** — Footer'da e-posta abonelik formu; doğrulama, honeypot, rate limit ve `firstOrCreate` ile **çift kayıt engelleme** içerir. Aboneler `newsletter_subscribers` tablosunda tutulur.
* **Zengin footer** — Güven bandı (4 hizmet rozeti), bülten bandı, marka/kategoriler/hesabım/sipariş takibi grid'i (sipariş takibi Alpine mini formu, backend gerektirmez) ve telif + ödeme yöntemi alt bandı. Stripe rozeti checkout ile aynı yapılandırma anahtarlarıyla denetlenir; yapılandırma yoksa yalnızca "Kapıda Ödeme" gösterilir. Zemin her temada koyu tutulur.
* **Sepet çekmecesi otomatik açılmıyor** — Listeden/ürün sayfasından sepete eklemede çekmece artık otomatik açılmaz (`cart-added` olayı ve `app.js` köprüsü kaldırıldı); yalnızca sepet rozeti güncellenir. Kullanıcı çekmeceyi kendisi açar; bu, `header`'daki mouse-over durumunu ve kazara açılmaları giderir.
* **Sitemap** — Kurumsal statik sayfalar (`/hakkimizda`, `/iletisim`, `/gizlilik`, `/iade-degisim`, `/sss`) sitemap'e eklendi.
* **CSS tarama kaynağı** — `storage/framework/views` `@source` satırı kaldırıldı: derlenmiş view'lar üretilen CSS'e sınıf katmıyor, yalnızca test koşularından biriken dosyalarla CSS'i şişiriyordu (build karşılaştırmasında SHA-256 birebir aynıydı).

### Kalite

* Yeni `HeaderGuestMenuTest`: misafirde "Kayıt ol" başlığı ile `/kayit` ve `/giris` bağlantıları görünür, giriş yapıldığında menü render edilmez (2 test / 8 assertion).
* Tam suite yeşil: **178 test / 1145 assertion**.

## [v1.4.0](https://github.com/exportdepo/lunar-exportdepo/releases/tag/v1.4.0) — 2026-09-23

Gelişmiş Koleksiyon Filtreleri, Etiketler ve Ürün Değerlendirmeleri (Phase 12)

### Yenilikler

* **Gelişmiş koleksiyon filtreleri** — Aynı ada sahip ürün seçenekleri (Renk, Beden) tek checkbox grubunda birleşir: grup içinde seçilenler OR, gruplar arası AND uygulanır. Marka ve etiket filtreleri çoklu seçimli (OR); koleksiyonda hiç eşleşmesi olmayan filtre grubu gizlenir. Fiyat/yenilik sıralaması ve filterable öznitelik filtresi eklenir; tüm filtreler başlangıçta kapalı açılır menü (accordion) grupları olarak sunulur ve okunabilir URL parametreleriyle paylaşılabilir (`q`, `brand`, `tags`, `options`, `attrs`, `sort`).
* **Ürün kartlarında etiket ve değerlendirme özeti** — Ürün kartlarında, istek listesinde ve ürün detay sayfasında ürün etiketleri rozet olarak; onaylanmış yorumlardan hesaplanan ortalama puan yıldız ve adetle gösterilir (yeni `StorefrontRatings` desteği).
* **Panelde etiket yönetimi** — Filament panelindeki ürün listesine "Etiketler" kolonu (renk kodlu rozetler) ve etiket filtresi eklenir (`ProductResource`).
* **Demo verisi** — Ürünler üç markaya (Lunar, Aurora, Nordika) deterministik dağıtılır; koleksiyon bazlı demo etiketleri idempotent biçimde eklenir.

### Düzeltmeler ve Bakım

* **Storefront animasyonları** — `anim-fade-up`/`stagger-in` belirme efektleri (`prefers-reduced-motion` emniyetiyle) ve navigasyonda soldan sağa açılan alt çizgi (`nav-underline`) eklenir; `bg-gradient-to-*` → `bg-linear-to-*` ve `aspect-[4/3]` → `aspect-4/3` (Tailwind v4).
* **IDE bakımı** — Laravel yardımcı stub'ı eklenir (`stubs/ide/laravel-helpers.php`; çalışma zamanına etkisi yoktur).

### Kalite

* Tam suite: **153 test / 714 assertion** yeşil.
* Filtre sistemi 14 senaryoyla güvence altında: seçenek gruplarının birleştirilmesi, çoklu seçim (OR/AND), marka/etiket/öznitelik filtreleri, sıralama, `#[Url]` hydration ve `clearFilters` davranışı.

## [v1.3.6](https://github.com/exportdepo/lunar-exportdepo/releases/tag/v1.3.6) — 2026-09-23

Sepet ve Sipariş Düzeltmeleri

### Yenilikler

* **Sepette varyant seçenekleri** — Sepet sayfası ve header sepet çekmecesi eklenen varyantın seçeneklerini ("Renk: Mavi") gösterir; sipariş durumu sayfası da satır bazında seçenekleri listeler.

### Düzeltmeler ve Bakım

* **Sepette stok sınırı** — Adet artırma, ürün detay sayfasıyla aynı kuralı uygular: stok takibi olan varyantta adet stoğu aşamaz. Lunar doğrulayıcılarının döndürdüğü `CartException` (ör. stok/adet katı kuralları) artık 500 yerine Türkçe uyarı olarak gösterilir ve adet kutusu sunucudaki değere geri döner.
* **Ödeme yöntemi etiketi** — Sipariş durumu sayfası `order.meta` alanını `AsArrayObject` cast'i ile okuduğu için Stripe siparişleri artık doğru şekilde "Kart ile ödeme (Stripe)" olarak etiketlenir (önceden bu dal hiç çalışmıyordu; Stripe akışı `stripe_session_id` ile tespit edilir).
* **Sipariş durumu sayfası** — Karanlık mod sınıfları eklendi; tutarlar siparişin para birimi simgesiyle gösterilir; adres kaydı yoksa bilgilendirici boş durum çıkar. Durum rozeti gömülü CSS doğrulayıcısını tetikleyen `style="... {{ ... }}"` yerine statik Tailwind sınıfı (`statusBadgeClass()`) kullanır.
* **`#[Locked]` sipariş özelliği** — Sipariş modeli istemci snapshot'ında değiştirilemez.
* **Sepet performansı** — Ürün görseli için `purchasable.product.media` önceden yüklenir (satır başına ek sorgu kaldırıldı) ve adet güncelleme akışında gereksiz çift `refresh()`/`recalculate()` temizlendi.
* **Bakım** — Kök dizindeki hata ayıklama artıkları (`all())`, `toArray())`) silindi; `.gitignore` deseni genişletildi. Değişen dosyalar Laravel Pint ile biçimlendirildi.

### Kalite

* Tam suite: **124 test / 548 assertion** yeşil.
* Yeni testler: stok üstü adedin reddi ve Türkçe uyarı, Lunar doğrulama hatasının uyarıya çevrilmesi, Stripe meta'sının etiketlenmesi, `#[Locked]` + typed model property'nin hydration turu, rozet Tailwind sınıf eşleşmesi (`statusBadgeClass()`).
* **IDE bakımı** — Livewire'ın runtime'da kaydettiği `View` makroları (`layoutData` vb.) için Intelephense yardım dosyası (`stubs/ide/livewire-view-macros.php`; çalışma zamanında yüklenmez, `composer autoload` dışında).

## [v1.3.5](https://github.com/exportdepo/lunar-exportdepo/releases/tag/v1.3.5) — 2026-09-22

Müşteri Yönetimi ve Storefront Cilası (Phase 11)

### Yenilikler

* **Panelde müşteri yönetimi** — Filament paneline `CustomerResource` eklendi: müşteriler listelenebilir, oluşturulabilir, düzenlenebilir ve görüntülenebilir (List/Create/Edit/View sayfaları).
* **Güvenli müşteri silme** — Varsayılan silme akışı, sipariş/sepet/adres/pivot tablolarındaki `on delete no action` yabancı anahtar kısıtları nedeniyle düşüyordu. Yeni `DeleteCustomers` servisi silme öncesi pivot ve adres kayıtlarını temizler; sipariş ve sepet referanslarını null'a çeker. **Sipariş geçmişi korunur.**
* **Test güvencesi** — `CustomerDeleteTest`: pivot bağlantılar + adres + sipariş + sepet içeren senaryoda tekli ve çoklu silme doğrulanır.

### Düzeltmeler ve Bakım

* **Storefront cilası** — Sepet ve ürün sayfalarında görsel/işlevsel dokunuşlar; `app.css` genişletmesi.
* **İstek listesi buton metni** — `İstek Listesine Ekle` → `İstek listesine ekle` (test güvencesiyle).

### Kalite

* İlgili test paketleri yeşil: Checkout + CustomerDelete + Wishlist + Stripe → 18 test / 97 assertion.
* Tam suite: 116 test / 519 assertion (tek seferlik tohumlama flake'i hariç; `UNIQUE constraint failed: lunar_currencies.code` ilgisiz, tek başına yeşil).

## [v1.2.0](https://github.com/exportdepo/lunar-exportdepo/releases/tag/v1.2.0) — 2026-09-17

Ürün Varyasyonları ve E-posta Normalizasyonu

### Yenilikler

* **Vitrinde ürün varyasyonları** — Renk ve beden gibi ürün seçenekleri seçilebilir. Eksik kombinasyonların kullanılabilirliği yönetilir; seçeneği olmayan ürünlerde varyant açılır listesi korunur.
* **Panelde seçenek yönetimi** — Filament panelinden ürün seçenekleri ve değerleri yönetilebilir; oluşturulan ürün varyantları görüntülenebilir ve düzenlenebilir.
* **Demo varyasyonlar** — Seçenek kombinasyonları, onaltılık (hex) renk kodları, fiyat farkları ve stok dışı örnekler eklendi. Veri oluşturmanın tekrar çalıştırıldığında kayıtları çoğaltmadığı testlerle doğrulanır.

### Düzeltmeler ve Bakım

* **Müşteri e-postalarının normalizasyonu** — Müşteri kimlik doğrulama akışlarında ve kullanıcı modelinde e-posta adreslerinin başındaki/sonundaki boşluklar temizlenir ve harfler küçültülür.
* **Mevcut müşteri e-postaları** — Veritabanı geçişi (migration), kayıtlı adresleri normalleştirir; çakışma durumunda hesapları birleştirmek yerine işlemi durdurur.
* **Demo müşteri kimlik bilgileri** — Demo veriler yeniden oluşturulduğunda mevcut müşteri parolaları ve profilleri korunur.
* **CI yapılandırması** — Zorunlu demo yönetici parolası yalnızca test adımına sağlanır; müşteri parolası test verilerini testlerin kendisi belirler.
* **Test kapsamı** — Varyasyonlar, panel seçenekleri, e-posta normalizasyonu ve demo kimlik bilgileri için yedi yeni Feature test dosyası eklendi.

### Yükseltme Notları

* Yükseltmeden önce veritabanını yedekleyin; ardından dağıtım ortamında `php artisan migrate --force` komutunu çalıştırın.
* E-posta normalizasyonu bir çakışma bildirirse yeniden denemeden önce ilgili hesapları manuel olarak düzenleyin. Migration hesapları birleştirmez; geri alma işlemi e-postaların önceki büyük/küçük harf biçimini veya boşluklarını geri getirmez.
* `DemoDataSeeder` çalıştırmak için ortamda `EXPORTDEPO_ADMIN_PASSWORD` tanımlı olmalıdır. Yalnızca CI testleri için kullanılan değeri değil, ortamınıza uygun gizli bir parola kullanın. Yalnızca migration uygulamak için demo veri oluşturmanız gerekmez.

### Doğrulama

* `6a4f170` commit'ine kadar olan uygulama ve CI düzeltmeleri için yerelde 97 test, 422 doğrulamayla başarılı oldu.
* GitHub Actions çalışması 35277419077 başarıyla tamamlandı: https://github.com/exportdepo/lunar-exportdepo/actions/runs/35277419077
* Bu sürüm, test edilmiş commit'in üzerine dokümantasyon ekler; sürüm notları commit'i uygulama kodunu değiştirmez.

## [v1.1.0](https://github.com/exportdepo/lunar-exportdepo/releases/tag/v1.1.0) — 2026-09-16

İstek Listesi ve Ürün Değerlendirmeleri

### Yenilikler

* **İstek listesi** — Misafirlerin listeleri oturumda, giriş yapan üyelerin listeleri veritabanında tutulur. Giriş yapıldığında oturumdaki liste kullanıcı hesabıyla birleştirilir.
* **İstek listesi sayfası** — `/istek-listesi` sayfası, `WishlistIndicator` sayacı ve vitrin sayfalarında listeye ekleme/çıkarma düğmeleri eklendi.
* **Ürün değerlendirmeleri** — Ürün sayfalarında onaylı değerlendirmeler ve puan ortalaması gösterilir. Panelden değerlendirmeleri onaylama/reddetme desteği eklendi.
* **Yönetim paneli adresi** — Panel `/lunar` adresinden `/exportdepo` adresine taşındı; `staff` kimlik doğrulama koruması ve sağlayıcısı kullanıldı. `PanelLoginTest` eklendi.
* **Kimlik doğrulama düzeltmeleri** — Giriş ve kayıt sayfalarındaki `mount` yönlendirmesi kaldırıldı; `guest` ara katmanı (`RedirectIfAuthenticated`) kullanıldı. Çıkış sırasında 419 hatasına karşı alternatif yönlendirme eklendi.
* **Demo içerik** — 17 ürün galerisi görseli ve örnek değerlendirme verileri eklendi. Demo hesap parolalarına ilişkin ortam değişkenleri `.env.example` dosyasında tanımlandı.

## [v1.0.0](https://github.com/exportdepo/lunar-exportdepo/releases/tag/v1.0.0) — 2026-09-15

İlk Sürüm

- Projenin ilk sürümü yayımlandı.
- [İlk sürümün commit geçmişi](https://github.com/exportdepo/lunar-exportdepo/commits/v1.0.0).
