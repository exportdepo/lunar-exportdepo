# ExportDepo E-Ticaret UI Yenileme ve Geliştirme Rehberi

Sevgili Cline, 
Bu belge, ExportDepo adlı headless e-ticaret (Lunar PHP tabanlı) projemizin vitrin (storefront) arayüzünü modernleştirmek için senin çalışma rehberindir. Lütfen aşağıdaki kurallara ve fazlara sıkı sıkıya bağlı kalarak geliştirmeleri yap.

## 1. Proje Bağlamı ve Teknoloji Yığını
- **Backend:** Laravel 12, PHP 8.3, Lunar PHP (Headless E-commerce)
- **Frontend Mimarisi:** Laravel Blade Şablonları (React veya Vue KULLANILMAYACAK)
- **Stil Motoru:** Tailwind CSS v4 (Vite ile derleniyor)
- **Etkileşim (Interactivity):** Alpine.js kullanılacak (Gerektiğinde npm üzerinden kurulacak)

## 2. Tasarım Prensipleri (Modern & Dönüşüm Odaklı)
- **Minimalizm:** Bol beyaz alan (whitespace) bırakılacak, içerik nefes alacak.
- **Tipografi:** Okunabilirliği yüksek, modern sans-serif fontlar kullanılacak.
- **Renk Paleti:** Markaya uygun, göz yormayan (light/dark mode uyumlu) Tailwind renk sınıfları (ör. `slate` veya `zinc` ağırlıklı) tercih edilecek. Aksiyon butonlarında dikkat çekici bir ana renk (primary) kullanılacak.
- **Mobil Öncelikli (Mobile-First):** Tüm tasarımlar önce mobil cihazlar için yapılacak, ardından `sm:`, `md:`, `lg:` prefixleri ile masaüstüne genişletilecek.
- **Modülerlik:** Tekrar eden her arayüz elemanı, Laravel Anonymous Blade Component (`resources/views/components/`) olarak oluşturulacak.

## 3. Geliştirme Aşamaları (Phases)

Lütfen benden onay almadan bir sonraki faza geçme. Her fazı bitirdiğinde bana rapor ver.

### Phase 1: Altyapı ve Ana İskelet (Layout) Hazırlığı
1. `npm install alpinejs` komutunu çalıştırarak Alpine.js'i projeye dahil et ve `resources/js/app.js` içerisinde yapılandır.
2. `resources/views/components/layouts/app.blade.php` adında ana bir layout bileşeni oluştur. Bu dosya; meta etiketleri, Tailwind/Vite direktiflerini (`@vite`) ve Alpine.js entegrasyonunu içermelidir.
3. Dark mode desteğini Tailwind yapılandırmasına ve layout'a ekle.

### Phase 2: Temel (Atomik) Bileşenlerin Üretimi
`resources/views/components/` dizini altında şu Blade bileşenlerini oluştur:
- `button.blade.php`: Farklı varyantları (primary, secondary, outline) olan, hover ve active efektlerine sahip modern bir buton.
- `input.blade.php`: Form girdileri için şık, hata durumlarını gösterebilen input tasarımı.
- `product-card.blade.php`: Ürün resmi, başlık, fiyat, ve "Sepete Ekle" butonu içeren. Üzerine gelindiğinde (hover) resmi hafifçe büyütecek veya değiştirecek modern bir kart.

### Phase 3: Navigasyon ve Sepet Çekmecesi (Drawer)
1. `header.blade.php`: Mobil uyumlu (hamburger menülü), logo, arama çubuğu ve sepet ikonunu barındıran yapışkan (sticky) bir üst navigasyon çubuğu oluştur.
2. `cart-drawer.blade.php`: Sepet ikonuna tıklandığında Alpine.js (`x-data`, `x-show`, `x-transition`) kullanarak ekranın sağından kayarak açılan modern bir sepet özet çekmecesi yap.

### Phase 4: Sayfa Şablonlarının Oluşturulması
1. **Ana Sayfa (`resources/views/home.blade.php`):** Çarpıcı bir Hero Section (Tam ekran banner) ve hemen altında "Öne Çıkan Ürünler" grid yapısını (Phase 2'deki ürün kartlarını kullanarak) kurgula.
2. **Kategori Listeleme Sayfası (PLP):** Sol tarafta filtreleme seçenekleri (fiyat, beden vb.), sağ tarafta ürünlerin grid şeklinde listelendiği yapı.
3. **Ürün Detay Sayfası (PDP):** Sol tarafta büyük ürün galerisi, sağ tarafta ürün detayları, varyant (beden/renk) seçici ve harekete geçirici (CTA) sepete ekle butonu.

## 4. Cline İçin Özel Komut
Bu dosyayı okuduysan, lütfen bana şu mesajı ver: *"ExportDepo UI yenileme planını anladım. Laravel Blade, Tailwind v4 ve Alpine.js kullanarak Phase 1'e başlamak için izninizi bekliyorum."*