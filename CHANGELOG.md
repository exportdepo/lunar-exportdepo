# Değişiklik Geçmişi

Önemli değişiklikler bu dosyada Türkçe olarak tutulur; sürümler yeniden eskiye sıralanır. Yayın tarihleri GitHub Releases kayıtlarına göre UTC tarihidir.

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
