<?php

namespace Database\Seeders;

use App\Models\ProductReview;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lunar\Admin\Models\Staff;
use Lunar\FieldTypes\Text;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Collection as ProductCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;
use Lunar\Models\Url;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    protected array $products = [
        ['slug' => 'lunarbook-pro-14', 'name' => 'LunarBook Pro 14', 'desc' => '14 inç Retina ekran, M-serisi işlemci, 16GB RAM, 512GB SSD.', 'price' => 32999, 'stock' => 25, 'collection' => 'elektronik', 'weight' => 1.4],
        ['slug' => 'kablosuz-mouse', 'name' => 'Kablosuz Mouse', 'desc' => 'Sessiz tıklama, USB-C şarj, 2.4GHz + Bluetooth çift mod.', 'price' => 599, 'stock' => 120, 'collection' => 'elektronik', 'weight' => 0.12],
        ['slug' => 'mekanik-klavye', 'name' => 'Mekanik Klavye', 'desc' => 'Türkçe Q layout, RGB aydınlatma, hot-swap switch, alüminyum kasa.', 'price' => 2499, 'stock' => 45, 'collection' => 'elektronik', 'weight' => 1.1],
        ['slug' => 'bluetooth-kulaklik', 'name' => 'Bluetooth Kulaklık', 'desc' => 'Aktif gürültü engelleme, 40 saat pil, Hi-Res Audio sertifikalı.', 'price' => 3999, 'stock' => 60, 'collection' => 'elektronik', 'weight' => 0.28],
        ['slug' => 'akilli-saat', 'name' => 'Akıllı Saat', 'desc' => 'AMOLED ekran, GPS, kalp ritmi, SpO2, 7 gün pil ömrü, 5ATM.', 'price' => 5499, 'stock' => 35, 'collection' => 'elektronik', 'weight' => 0.05],
        ['slug' => 'premium-pamuk-tisort', 'name' => 'Premium Pamuk Tişört', 'desc' => '%100 organik pamuk, slim fit, 200gr kumaş.', 'price' => 349, 'stock' => 200, 'collection' => 'giyim', 'weight' => 0.2],
        ['slug' => 'yaz-elbisesi', 'name' => 'Yaz Elbisesi', 'desc' => 'Hafif viskon kumaş, çiçekli desen, midi boy.', 'price' => 799, 'stock' => 75, 'collection' => 'giyim', 'weight' => 0.3],
        ['slug' => 'slim-fit-kot', 'name' => 'Slim Fit Kot Pantolon', 'desc' => 'Esnek kumaş, klasik kesim, koyu yıkama.', 'price' => 899, 'stock' => 90, 'collection' => 'giyim', 'weight' => 0.55],
        ['slug' => 'spor-ayakkabi', 'name' => 'Spor Ayakkabı', 'desc' => 'Hafif tasarım, nefes alabilen file üst, EVA taban.', 'price' => 1499, 'stock' => 110, 'collection' => 'giyim', 'weight' => 0.7],
        ['slug' => 'yun-kazak', 'name' => 'Yün Kazak', 'desc' => 'Merinos yünü, kazak yaka, klasik kesim.', 'price' => 1299, 'stock' => 50, 'collection' => 'giyim', 'weight' => 0.5],
        ['slug' => 'otomatik-kahve-makinesi', 'name' => 'Otomatik Kahve Makinesi', 'desc' => '15 bar basınç, süt köpürtücü, çift fincan, dokunmatik panel.', 'price' => 12999, 'stock' => 15, 'collection' => 'ev-yasam', 'weight' => 8.5],
        ['slug' => 'seramik-tava-seti', 'name' => 'Seramik Tava Seti', 'desc' => '3 parça: 20cm, 26cm, 28cm. Yapışmaz seramik kaplama.', 'price' => 1899, 'stock' => 40, 'collection' => 'ev-yasam', 'weight' => 3.2],
        ['slug' => 'bel-yastigi', 'name' => 'Bel Yastığı (Memory Foam)', 'desc' => 'Visko elastik sünger, ergonomik tasarım, ofis koltuğu için.', 'price' => 449, 'stock' => 80, 'collection' => 'ev-yasam', 'weight' => 0.4],
        ['slug' => 'led-masa-lambasi', 'name' => 'LED Masa Lambası', 'desc' => '5 renk sıcaklık, 10 parlaklık seviye, USB-C şarj.', 'price' => 699, 'stock' => 65, 'collection' => 'ev-yasam', 'weight' => 0.8],
        ['slug' => 'seramik-saksi', 'name' => 'Seramik Saksı Seti', 'desc' => '3 parça farklı boy, beyaz mat, drenaj delikli.', 'price' => 549, 'stock' => 55, 'collection' => 'ev-yasam', 'weight' => 1.8],
    ];

    /**
     * Seçenek handle'larının panel ve vitrindeki görünen adları.
     *
     * @var array<string, string>
     */
    protected array $optionLabels = [
        'renk' => 'Renk',
        'beden' => 'Beden',
        'numara' => 'Numara',
        'paca-boyu' => 'Paça Boyu',
        'switch-tipi' => 'Switch Tipi',
        'klavye-dili' => 'Klavye Dili',
        'kordon' => 'Kordon',
        'model' => 'Model',
    ];

    /**
     * Ürün bazlı varyasyon tanımları (renk, beden, model vb.).
     *
     * Burada listelenen ürünlerde seçenek kombinasyonları kadar varyant üretilir;
     * listelenmeyen ürünler tek varyantlı kalır (geriye dönük uyum).
     *
     * Her değer şu anahtarları taşır:
     *   - name        : görünen ad (vitrin + panel)
     *   - hex         : yalnızca renk seçeneklerinde gösterilen renk kodu (swatch)
     *   - price_delta : baz fiyata TL cinsinden eklenen fark
     *   - stock       : sabit stok (verilmezse baz stoktan türetilir)
     *
     * @var array<string, array<string, array<int, array<string, mixed>>>>
     */
    protected array $variations = [
        'premium-pamuk-tisort' => [
            'renk' => [
                ['name' => 'Siyah', 'hex' => '#111827', 'price_delta' => 0],
                ['name' => 'Beyaz', 'hex' => '#f1f5f9', 'price_delta' => 0],
                ['name' => 'Lacivert', 'hex' => '#1d4ed8', 'price_delta' => 20],
                ['name' => 'Bordo', 'hex' => '#7f1d1d', 'price_delta' => 20],
            ],
            'beden' => [
                ['name' => 'S', 'price_delta' => 0],
                ['name' => 'M', 'price_delta' => 0],
                ['name' => 'L', 'price_delta' => 0],
                ['name' => 'XL', 'price_delta' => 10],
                ['name' => '2XL', 'price_delta' => 30, 'stock' => 0],
            ],
        ],
        'yaz-elbisesi' => [
            'renk' => [
                ['name' => 'Çiçekli Pembe', 'hex' => '#ec4899', 'price_delta' => 0],
                ['name' => 'Çiçekli Mavi', 'hex' => '#3b82f6', 'price_delta' => 0],
                ['name' => 'Zeytin Yeşili', 'hex' => '#4d7c0f', 'price_delta' => 50],
            ],
            'beden' => [
                ['name' => 'XS', 'price_delta' => 0],
                ['name' => 'S', 'price_delta' => 0],
                ['name' => 'M', 'price_delta' => 0],
                ['name' => 'L', 'price_delta' => 20],
            ],
        ],
        'yun-kazak' => [
            'renk' => [
                ['name' => 'Antrasit', 'hex' => '#374151', 'price_delta' => 0],
                ['name' => 'Camel', 'hex' => '#b45309', 'price_delta' => 40],
                ['name' => 'Krem', 'hex' => '#fef3c7', 'price_delta' => 0],
            ],
            'beden' => [
                ['name' => 'S', 'price_delta' => 0],
                ['name' => 'M', 'price_delta' => 0],
                ['name' => 'L', 'price_delta' => 0],
                ['name' => 'XL', 'price_delta' => 30],
            ],
        ],
        'slim-fit-kot' => [
            'beden' => [
                ['name' => '30/32', 'price_delta' => 0],
                ['name' => '31/32', 'price_delta' => 0],
                ['name' => '32/32', 'price_delta' => 0],
                ['name' => '33/32', 'price_delta' => 0],
                ['name' => '34/32', 'price_delta' => 30],
                ['name' => '36/32', 'price_delta' => 60, 'stock' => 0],
            ],
            'paca-boyu' => [
                ['name' => 'Standart Boy', 'price_delta' => 0],
                ['name' => 'Uzun Boy', 'price_delta' => 40],
                ['name' => 'Kısa Boy', 'price_delta' => 40],
            ],
        ],
        'spor-ayakkabi' => [
            'renk' => [
                ['name' => 'Beyaz', 'hex' => '#f1f5f9', 'price_delta' => 0],
                ['name' => 'Siyah', 'hex' => '#111827', 'price_delta' => 0],
                ['name' => 'Gri', 'hex' => '#6b7280', 'price_delta' => 50],
            ],
            'numara' => [
                ['name' => '38', 'price_delta' => 0],
                ['name' => '39', 'price_delta' => 0],
                ['name' => '40', 'price_delta' => 0],
                ['name' => '41', 'price_delta' => 0],
                ['name' => '42', 'price_delta' => 0],
                ['name' => '44', 'price_delta' => 100, 'stock' => 0],
            ],
        ],
        'mekanik-klavye' => [
            'switch-tipi' => [
                ['name' => 'Red (Linear)', 'price_delta' => 0],
                ['name' => 'Brown (Tactile)', 'price_delta' => 150],
                ['name' => 'Blue (Clicky)', 'price_delta' => 150],
            ],
            'klavye-dili' => [
                ['name' => 'Türkçe Q', 'price_delta' => 0],
                ['name' => 'İngilizce US', 'price_delta' => 0],
            ],
        ],
        'bluetooth-kulaklik' => [
            'renk' => [
                ['name' => 'Siyah', 'hex' => '#111827', 'price_delta' => 0],
                ['name' => 'Beyaz', 'hex' => '#f1f5f9', 'price_delta' => 0],
                ['name' => 'Lila', 'hex' => '#a855f7', 'price_delta' => 100],
            ],
        ],
        'akilli-saat' => [
            'renk' => [
                ['name' => 'Siyah', 'hex' => '#111827', 'price_delta' => 0],
                ['name' => 'Gümüş', 'hex' => '#cbd5e1', 'price_delta' => 100],
                ['name' => 'Altın', 'hex' => '#d4af37', 'price_delta' => 250],
            ],
            'kordon' => [
                ['name' => 'Silikon', 'price_delta' => 0],
                ['name' => 'Deri', 'price_delta' => 300],
            ],
        ],
        'kablosuz-mouse' => [
            'renk' => [
                ['name' => 'Siyah', 'hex' => '#111827', 'price_delta' => 0],
                ['name' => 'Beyaz', 'hex' => '#f1f5f9', 'price_delta' => 0],
                ['name' => 'Turkuaz', 'hex' => '#06b6d4', 'price_delta' => 30],
            ],
        ],
        'otomatik-kahve-makinesi' => [
            'model' => [
                ['name' => 'Mat Siyah', 'price_delta' => 0],
                ['name' => 'Paslanmaz Çelik', 'price_delta' => 1500],
            ],
        ],
    ];

    /** @var array<int, array<string, string>> */
    protected array $collections = [
        ['handle' => 'elektronik', 'name' => 'Elektronik', 'desc' => 'Bilgisayar, telefon, aksesuar ve diğer teknoloji ürünleri.'],
        ['handle' => 'giyim', 'name' => 'Giyim', 'desc' => 'Kadın, erkek ve unisex giyim ürünleri.'],
        ['handle' => 'ev-yasam', 'name' => 'Ev & Yaşam', 'desc' => 'Ev dekorasyonu, mutfak ve yaşam alanlarınız için ürünler.'],
    ];

    /** @var array<string, array<int, string>> */
    protected array $productFeatures = [
        'lunarbook-pro-14' => ['14 inç Retina ekran (2560×1600)', 'M-serisi işlemci', '16GB DDR5 RAM', '512GB NVMe SSD', '1,4 kg ağırlık', 'Tam gün batarya ömrü'],
        'kablosuz-mouse' => ['Sessiz tıklama teknolojisi', '2.4GHz + Bluetooth çift mod', 'USB-C hızlı şarj', '6 aya kadar pil ömrü', 'Ergonomik simetrik tasarım'],
        'mekanik-klavye' => ['Türkçe Q tuş dizilimi', 'RGB aydınlatma (16.8M renk)', 'Hot-swap switch desteği', 'Alüminyum kasa', 'N-key rollover', 'Çıkarılabilir USB-C kablo'],
        'bluetooth-kulaklik' => ['Aktif gürültü engelleme (ANC)', '40 saat pil ömrü', 'Hi-Res Audio sertifikalı', 'Çok noktalı bağlantı', 'Katlanır taşıma tasarımı'],
        'akilli-saat' => ['1.4 inç AMOLED ekran', 'Dahili GPS', 'Kalp ritmi ve SpO2 takibi', '7 gün pil ömrü', '5ATM su geçirmezlik', '100+ spor modu'],
        'premium-pamuk-tisort' => ['%100 organik pamuk', '200 gr/m² kumaş', 'Slim fit kalıp', 'Çift iğne dikiş yaka ve kol', '30°C makinede yıkama'],
        'yaz-elbisesi' => ['Hafif viskon kumaş', 'Çiçekli desen', 'Midi boy', 'Volanlı kol detayı', 'Kemerli ve kemersiz kullanım'],
        'slim-fit-kot' => ['Esnek denim (2% elastan)', 'Slim fit kalıp', 'Koyu yıkama rengi', 'Klasik 5 cep tasarım', 'Ayarlanabilir paça'],
        'spor-ayakkabi' => ['Nefes alan file üst yüzey', 'Hafif EVA ara taban', 'Kauçuk dış taban', 'Çıkarılabilir iç taban', 'Yaklaşık 280 gram (42 no)'],
        'yun-kazak' => ['%100 merinos yünü', 'Kazak yaka', 'Klasik kesim', 'Kaşıntı yapmayan lif yapısı', 'Elde yıkama önerilir'],
        'otomatik-kahve-makinesi' => ['15 bar pompa basıncı', 'Otomatik süt köpürtücü', 'Çift fincan aynı anda', 'Dokunmatik kontrol paneli', 'Yıkanabilir demlik grubu', '1.8 L su deposu'],
        'seramik-tava-seti' => ['3 parça: 20, 26 ve 28 cm', 'Yapışmaz seramik kaplama', 'İndüksiyon dahil tüm ocaklar', 'Isı iletmez kulplar', 'Bulaşık makinesinde yıkanabilir'],
        'bel-yastigi' => ['Visko elastik sünger', 'Ergonomik bel kavisi', 'Fermuarlı yıkanabilir kılıf', 'Ofis ve araç uyumlu', '40×40 cm ölçü'],
        'led-masa-lambasi' => ['5 renk sıcaklığı (2700K-6500K)', '10 parlaklık kademesi', 'USB-C şarj, kablosuz kullanım', 'Dokunmatik kontrol', 'Titreşimsiz göz koruyucu ışık'],
        'seramik-saksi' => ['3 farklı boy (15, 20, 25 cm)', 'Beyaz mat sırlı yüzey', 'Drenaj deliği + alt tabak', 'Elde üretilmiş kil seramik', 'İç mekân ve balkon uyumlu'],
    ];

    /** @var array<string, string> */
    protected array $productDescriptions = [
        'lunarbook-pro-14' => 'LunarBook Pro 14, 14 inç Retina kalitesinde ekranı ve M-serisi işlemcisiyle günlük işlerden profesyonel projelere kadar her yükü kolayca kaldırır. 16GB RAM ve 512GB SSD sayesinde çoklu sekme, video düzenleme ve büyük dosyalar arasındaki geçişler anlık olur. 1,4 kg\'lık alüminyum gövde çantada fark ettirmeden taşınır; tam gün süren batarya ile priz aramaya son.',
        'kablosuz-mouse' => 'Kablosuz Mouse, sessiz tıklama yapısıyla gece çalışmaları ve paylaşılan ofisler için ideal bir seçim. 2.4GHz alıcı veya Bluetooth arasında tek tuşla geçiş yapabilir, USB-C ile 10 dakikalık şarjla gün boyu kullanabilirsiniz. Simetrik gövdesi sağ ve sol el kullanıcılarına aynı konforu sunar.',
        'mekanik-klavye' => 'Mekanik Klavye, Türkçe Q dizilimi ve hot-swap anahtar yuvalarıyla hem günlük kullanıcılara hem de modifiye meraklılarına hitap eder. Alüminyum kasa ve N-key rollover sayesinde en hızlı anlarda bile tuş kaybı yaşanmaz. RGB aydınlatmayı yazılımla senkronize edip kendi ışık profillerinizi oluşturabilirsiniz.',
        'bluetooth-kulaklik' => 'Bluetooth Kulaklık, aktif gürültü engelleme ile uçuşta, metroda ve açık ofiste sessiz bir alan yaratır. Hi-Res Audio sertifikalı sürücüler detayları kaybetmeden aktarır; tek şarjla 40 saate kadar dinleme süresi sunar. Katlanır gövdesi ve taşıma kılıfıyla ideal bir yol arkadaşıdır.',
        'akilli-saat' => 'Akıllı Saat, parlak AMOLED ekranı ve dahili GPS\'i ile koşudan yüzmeye kadar tüm antrenmanlarınızı kaydeder. Kalp ritmi, SpO2 ve uyku analizleriyle sağlığınızı 7/24 takip eder; tek şarj 7 gün yeter. 5ATM sertifikası sayesinde yüzme seanslarında gönül rahatlığıyla kullanabilirsiniz.',
        'premium-pamuk-tisort' => 'Premium Pamuk Tişört, %100 organik pamuktan üretilen 200 gr/m² kalın dokusuyla hem yumuşak hem de formunu koruyan bir yapıya sahiptir. Slim fit kalıbı bedeninizi gösterirken rahat hareket alanı bırakır. Kol ve yaka biyeleri, yıkamalarda esnemeyen çift iğne dikişle güçlendirilmiştir.',
        'yaz-elbisesi' => 'Yaz Elbisesi, nefes alan viskon dokusuyla sıcak günlerde serin ve şık kalmanızı sağlar. Midi boy ve volanlı kol detayı hem ofiste hem plajda uyum sağlar. Kemerle bel vurgusu yapabilir, kemersiz daha rahat bir siluet tercih edebilirsiniz.',
        'slim-fit-kot' => 'Slim Fit Kot Pantolon, esnek denim dokusuyla gün boyu konfor sunar; klasik beş cep yapısıyla zamansızdır. Koyu yıkama rengi hem ofis hem hafta sonu kombinlerine kolayca uyum sağlar. Paça, ütüyle istediğiniz boya getirilebilir.',
        'spor-ayakkabi' => 'Spor Ayakkabı, file üst yüzeyi ve EVA ara tabanıyla adım başı enerji tasarrufu sağlar. Kauçuk dış taban, ıslak zeminlerde güvenli tutuş sunar. Çıkarılabilir iç taban sayesinde kişisel taban desteğiyle kullanılabilir; 280 gram ağırlığıyla koşu ve günlük kullanım için idealdir.',
        'yun-kazak' => 'Yün Kazak, merinos yününün doğal ısı düzenleyici özelliğiyle soğuk günlerde terletmeden sıcak tutar. Kazak yaka ve klasik kesim, gömlek üstüne veya tek başına rahatlıkla kombinlenir. Merinos lifleri, çapraz lif yapısı sayesinde kaşıntı yapmaz.',
        'otomatik-kahve-makinesi' => 'Otomatik Kahve Makinesi, 15 bar basınçlı pompasıyla espresso keyfini evinize taşır. Otomatik süt köpürtücü tek tuşla cappuccino ve latte hazırlar; çift fincan fonksiyonu misafirlerinizi bekletmez. Yıkanabilir demlik grubu sayesinde bakım dakikalar içinde tamamlanır.',
        'seramik-tava-seti' => 'Seramik Tava Seti, üç farklı boyutuyla her yemek için doğru tavayı sunar. Yapışmaz seramik kaplama az yağla pişirmenizi sağlar ve yanık kalıntısı bırakmaz. İndüksiyon dahil tüm ocaklarda çalışır; kulplar ısı iletmez, bulaşık makinesinde rahatça yıkanır.',
        'bel-yastigi' => 'Bel Yastığı, visko elastik süngeriyle belinize şekil alır ve uzun oturmalarda yükü omurgadan alır. Fermuarlı kılıfı çıkarılıp makinede yıkanabilir; dikişsiz kumaş yüzeyi terletmez. Ofis koltuğu, araç koltuğu ve ergonomik sandalyelerle uyumludur.',
        'led-masa-lambasi' => 'LED Masa Lambası, beş renk sıcaklığı ve on parlaklık kademesiyle okumadan videokonferansa her ortam için doğru ışığı verir. Titreşimsiz LED teknolojisi göz yorgunluğunu azaltır. Dahili bataryası USB-C ile şarj edildikten sonra kablosuz olarak da kullanılabilir.',
        'seramik-saksi' => 'Seramik Saksı Seti, üç farklı boyutuyla sukulentten sarmaşığa kadar tüm bitki ihtiyaçlarını karşılar. Beyaz mat sırlı yüzey minimalist ev dekoruyla uyumludur. Alt tabaklı drenaj deliği kök çürümesini önler; her saksı elde ayrı ayrı sırlanır.',
    ];

    /** @var array<int, string> */
    protected array $reviewTemplates = [
        'Ürün beklentimin üzerinde çıktı; {name} için bu fiyata bu kalite gerçekten iyi. Kargo özenliydi, tavsiye ederim.',
        '{name} tam anlatıldığı gibi geldi. Üç gün içinde teslim edildi, kutusu hasarsızdı. Teşekkürler!',
        'İki haftadır kullanıyorum, oldukça memnunum. Küçük eksikleri olsa da kesinlikle alırım.',
        'Kalitesi güzel ama rengi fotoğraftakinden biraz daha koyu görünüyor. Yine de beğendim.',
        'Ailemden de alan oldu, herkes memnun. İade sürecini de test ettik, sorunsuz çalışıyor.',
        'Fiyat/performans olarak harika. Hızlı teslimat ve detaylara gösterilen özen için ayrıca teşekkürler.',
    ];

    /** @var array<int, string> */
    protected array $reviewerNames = ['Ayşe K.', 'Mehmet D.', 'Zeynep A.', 'Can Y.', 'Elif T.', 'Burak S.', 'Deniz Ö.', 'Selin M.'];

    /** @var array<int, string> */
    protected array $reviewTitles = ['Harika ürün', 'Beklentiyi karşıladı', 'Memnun kaldım', 'Kaliteli ürün', 'Tekrar alırım', 'Tavsiye ederim'];

    /** @var array<int, int> */
    protected array $reviewRatings = [5, 4, 5, 5, 4, 3];

    public function run(): void
    {
        $this->command->info('🌙 Lunar Demo Veri Tohumlama Başlıyor...');

        $this->ensureDemoImages();
        $this->command->info('  ✓ 15 placeholder görsel hazır');

        DB::transaction(function () {
            $channel = $this->ensureChannel();
            $currency = $this->ensureDefaultCurrency();
            $language = $this->ensureDefaultLanguage();
            $taxClass = $this->ensureDefaultTaxClass();
            $this->ensureDefaultTaxZone($taxClass);
            $customerGroup = $this->ensureDefaultCustomerGroup();
            $productType = $this->ensureDefaultProductType();
            $collectionGroup = $this->ensureDefaultCollectionGroup();
            $brand = $this->ensureBrand();
            $this->ensureFeaturesAttribute();

            $this->command->info('  ✓ Kanal, para birimi, dil, KDV, marka hazır');

            $collections = $this->createCollections($collectionGroup, $language);
            $this->command->info('  ✓ 3 koleksiyon oluşturuldu');

            $count = $this->createProducts($productType, $brand, $taxClass, $currency, $customerGroup, $channel, $language, $collections);
            $this->command->info("  ✓ {$count} ürün + varyant + fiyat + stok + görsel eklendi");

            $this->createAdminUser();
            $this->command->info('  ✓ Admin kullanıcı oluşturuldu');
            $this->ensureLegacyStaff();
            $this->command->info('  ✓ Eski panel hesabı senkronize edildi');

            $this->createTestCustomer($customerGroup);
            $this->command->info('  ✓ Test müşteri oluşturuldu');
            $this->ensureLegacyCustomer($customerGroup);
            $this->command->info('  ✓ Eski müşteri hesabı senkronize edildi');

            $this->pruneForeignProducts($this->products, $channel, $currency, $customerGroup);
            $this->pruneForeignCollections($collectionGroup, $language);

            $this->createDemoWishlist();
            $this->command->info('  ✓ Demo istek listesi oluşturuldu');
        });

        $this->command->info('✅ Demo veri başarıyla yüklendi!');
        $this->command->newLine();
        $this->command->info('🔐 Admin: admin@exportdepo.test / [EXPORTDEPO_ADMIN_PASSWORD]');
        $this->command->info('👤 Müşteri: musteri@test.com / password');
    }

    /**
     * 15 placeholder SVG görseli PHP runtime'ında oluşturur.
     * (Windows opcache yol cache sorununu önlemek için çalışma zamanında yapılır)
     */
    protected function ensureDemoImages(): void
    {
        $dir = public_path('images/demo');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $colorMap = [
            'lunarbook-pro-14' => '#3b82f6', 'kablosuz-mouse' => '#0ea5e9', 'mekanik-klavye' => '#6366f1',
            'bluetooth-kulaklik' => '#8b5cf6', 'akilli-saat' => '#06b6d4',
            'premium-pamuk-tisort' => '#ef4444', 'yaz-elbisesi' => '#ec4899', 'slim-fit-kot' => '#1e40af',
            'spor-ayakkabi' => '#f97316', 'yun-kazak' => '#a16207',
            'otomatik-kahve-makinesi' => '#92400e', 'seramik-tava-seti' => '#dc2626', 'bel-yastigi' => '#16a34a',
            'led-masa-lambasi' => '#eab308', 'seramik-saksi' => '#65a30d',
        ];

        // Ana görsel + 2 galeri varyantı (farklı gradyan yönü ve köşe rozeti)
        $views = [
            ['suffix' => '', 'x1' => '0%', 'y1' => '0%', 'x2' => '100%', 'y2' => '100%', 'badge' => ''],
            ['suffix' => '-gorsel-2', 'x1' => '100%', 'y1' => '0%', 'x2' => '0%', 'y2' => '100%', 'badge' => 'Görünüm 2'],
            ['suffix' => '-gorsel-3', 'x1' => '0%', 'y1' => '100%', 'x2' => '100%', 'y2' => '0%', 'badge' => 'Görünüm 3'],
        ];

        foreach ($this->products as $p) {
            $color = $colorMap[$p['slug']] ?? '#6b7280';

            foreach ($views as $view) {
                $path = $dir.DIRECTORY_SEPARATOR.$p['slug'].$view['suffix'].'.svg';
                $badge = $view['badge'] !== ''
                    ? '<rect x="430" y="24" rx="12" width="150" height="36" fill="rgba(255,255,255,0.25)"/>'
                        .'<text x="505" y="48" font-size="18" text-anchor="middle" fill="white" font-family="Arial">'.$view['badge'].'</text>'
                    : '';
                $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">'
                    .'<defs><linearGradient id="bg" x1="'.$view['x1'].'" y1="'.$view['y1'].'" x2="'.$view['x2'].'" y2="'.$view['y2'].'">'
                    .'<stop offset="0%" style="stop-color:'.$color.';stop-opacity:1"/>'
                    .'<stop offset="100%" style="stop-color:rgba(0,0,0,0.4);stop-opacity:1"/>'
                    .'</linearGradient></defs>'
                    .'<rect width="600" height="600" fill="url(#bg)"/>'
                    .'<circle cx="300" cy="240" r="110" fill="rgba(255,255,255,0.15)"/>'
                    .'<text x="300" y="280" font-size="40" text-anchor="middle" fill="white" font-family="Arial">'.htmlspecialchars($p['name']).'</text>'
                    .'<text x="300" y="340" font-size="100" text-anchor="middle" fill="rgba(255,255,255,0.5)">📦</text>'
                    .'<text x="300" y="450" font-family="Arial" font-size="22" text-anchor="middle" fill="rgba(255,255,255,0.85)">ExportDepo</text>'
                    .$badge
                    .'</svg>';
                file_put_contents($path, $svg);
            }
        }
    }

    // ────────────────────────────────────────────────────────────
    // Defaults (idempotent — tekrar çalıştırılabilir)
    // ────────────────────────────────────────────────────────────

    protected function ensureChannel(): Channel
    {
        $channel = Channel::firstOrCreate(
            ['handle' => 'web'],
            ['name' => 'Web', 'url' => config('app.url'), 'default' => true]
        );

        if ($channel->url !== config('app.url')) {
            $channel->update(['url' => config('app.url')]);
        }

        Channel::whereKeyNot($channel->id)->where('default', true)->update(['default' => false]);
        if (! $channel->default) {
            $channel->update(['default' => true]);
        }

        return $channel;
    }

    protected function ensureDefaultCurrency(): Currency
    {
        $try = Currency::where('code', 'TRY')->first();
        if ($try) {
            if (! $try->default) {
                $try->update(['default' => true]);
            }

            return $try;
        }
        $existingDefault = Currency::where('default', true)->first();
        if ($existingDefault) {
            $existingDefault->update(['default' => false]);
        }

        return Currency::create([
            'code' => 'TRY', 'name' => 'Türk Lirası',
            'exchange_rate' => 1, 'decimal_places' => 2, 'default' => true, 'enabled' => true,
        ]);
    }

    protected function ensureDefaultLanguage(): Language
    {
        return Language::firstOrCreate(
            ['code' => 'tr'],
            ['name' => 'Türkçe', 'default' => true]
        );
    }

    protected function ensureDefaultTaxClass(): TaxClass
    {
        return TaxClass::firstOrCreate(['name' => 'KDV'], ['default' => true]);
    }

    /**
     * Varsayılan vergi bölgesi ve KDV oranı.
     *
     * Lunar cart pipeline'ı (SystemTaxDriver::getBreakdown) GetTaxZone
     * fallback'iyle default bölgeyi arar; tanımsızsa "Attempt to read
     * property \"id\" on null" hatasıyla vitrinin tüm sayfaları
     * (sepette ürün olduğu sürece) 500 döndürür.
     */
    protected function ensureDefaultTaxZone(TaxClass $taxClass): void
    {
        $country = Country::firstOrCreate(
            ['iso3' => 'TUR'],
            [
                'name' => 'Türkiye',
                'iso2' => 'TR',
                'phonecode' => '+90',
                'currency' => 'TRY',
                'native' => 'Türkiye',
                'emoji' => '🇹🇷',
                'emoji_u' => 'TR',
            ]
        );

        $zone = TaxZone::firstOrCreate(
            ['name' => 'Türkiye'],
            [
                'zone_type' => 'country',
                'price_display' => 'tax_exclusive',
                'active' => true,
                'default' => true,
            ]
        );

        TaxZoneCountry::firstOrCreate([
            'tax_zone_id' => $zone->id,
            'country_id' => $country->id,
        ]);

        $rate = TaxRate::firstOrCreate(
            ['tax_zone_id' => $zone->id, 'name' => 'KDV %20'],
            ['priority' => 1]
        );

        TaxRateAmount::firstOrCreate(
            ['tax_rate_id' => $rate->id, 'tax_class_id' => $taxClass->id],
            ['percentage' => 20]
        );
    }

    protected function ensureDefaultCustomerGroup(): CustomerGroup
    {
        return CustomerGroup::firstOrCreate(
            ['handle' => 'default'],
            ['name' => 'Standart Müşteriler', 'default' => true]
        );
    }

    protected function ensureDefaultProductType(): ProductType
    {
        return ProductType::firstOrCreate(['name' => 'Stock']);
    }

    protected function ensureDefaultCollectionGroup(): CollectionGroup
    {
        return CollectionGroup::firstOrCreate(
            ['name' => 'Ana Koleksiyonlar'],
            ['handle' => 'ana-koleksiyonlar']
        );
    }

    protected function ensureBrand(): Brand
    {
        return Brand::firstOrCreate(['name' => 'Lunar']);
    }

    // ────────────────────────────────────────────────────────────
    // Collections
    // ────────────────────────────────────────────────────────────

    /**
     * Koleksiyonları ad çevirisi üzerinden idempotent eşleştirir.
     *
     * NOT: `lunar_collections` tablosunda `handle` kolonu YOKTUR (sadece
     * attribute_data JSON içinde `name` + URL slug vardır). Eski kod
     * `where('handle', ...)` ile sorguladığı için eşleşme hiç bulunamıyor
     * ve her seed koşusunda 3 kopya üretiliyordu.
     *
     * @return array<string, ProductCollection>
     */
    protected function createCollections(CollectionGroup $group, Language $language): array
    {
        $created = [];
        $channel = Channel::where('handle', 'web')->first();
        $cg = CustomerGroup::where('handle', 'default')->first();
        $collectionMorph = (new ProductCollection)->getMorphClass();

        foreach ($this->collections as $col) {
            // 1) Öncelik: aynı grup içinde ad çevirisi eşleşmesi (kullanıcı seçimi).
            $collection = $this->findCollectionByName($group->id, $col['name']);

            // 2) Fallback: aynı slug'a sahip URL üzerinden eşleştirme
            // (eski kopyalardan birini canonical olarak benimser, yenisini üretmez).
            if (! $collection) {
                $collectionId = Url::where('element_type', $collectionMorph)
                    ->where('slug', $col['handle'])
                    ->whereHasMorph('element', [ProductCollection::class], function ($query) use ($group) {
                        $query->where('collection_group_id', $group->id);
                    })
                    ->value('element_id');

                $collection = $collectionId ? ProductCollection::find($collectionId) : null;
            }

            if (! $collection) {
                $collection = ProductCollection::create([
                    'collection_group_id' => $group->id,
                    'attribute_data' => collect([
                        'name' => new Text($col['name']),
                        'description' => new Text($col['desc']),
                    ]),
                ]);
            } else {
                // Mevcut kaydı güncel tut (yeniden üretmeden).
                $collection->update([
                    'collection_group_id' => $group->id,
                    'attribute_data' => collect([
                        'name' => new Text($col['name']),
                        'description' => new Text($col['desc']),
                    ]),
                ]);
            }

            $collection->channels()->syncWithoutDetaching([$channel->id]);
            $collection->customerGroups()->syncWithoutDetaching([$cg->id]);

            Url::firstOrCreate(
                [
                    'element_type' => $collectionMorph,
                    'element_id' => $collection->id,
                    'language_id' => $language->id,
                ],
                ['slug' => $col['handle'], 'default' => true]
            );

            // Koleksiyona ilk ürünün svg'sini kapak görseli olarak ekle
            $firstProduct = collect($this->products)->firstWhere('collection', $col['handle']);
            if ($firstProduct) {
                $imgPath = public_path("images/demo/{$firstProduct['slug']}.svg");
                if (file_exists($imgPath) && $collection->media()->count() === 0) {
                    $collection->addMedia($imgPath)
                        ->preservingOriginal()
                        ->usingName($col['name'])
                        ->toMediaCollection('images');
                }
            }

            $created[$col['handle']] = $collection;
        }

        return $created;
    }

    /**
     * Ayni koleksiyon grubu icinde ad cevirisi eslesen koleksiyonu bulur.
     *
     * `attribute_data` JSON yapisi DB-motoruna gore farkli davrandigi icin
     * ham JSON sorgusu yerine PHP tarafinda translateAttribute karsilastirmasi
     * yapilir. Koleksiyon sayisi kucuk oldugundan bu guvenlidir.
     */
    protected function findCollectionByName(int $groupId, string $name): ?ProductCollection
    {
        $wanted = mb_strtolower(trim($name));

        return ProductCollection::where('collection_group_id', $groupId)
            ->get()
            ->first(function (ProductCollection $collection) use ($wanted) {
                $current = $collection->translateAttribute('name');

                return $current !== null && mb_strtolower(trim((string) $current)) === $wanted;
            });
    }

    /**
     * Liste disi / kopya koleksiyonlari budar.
     *
     * Canonical: once ad eslesmesi, yoksa ilk slug kaydi. Kopyanin urun
     * pivotlari, kanal/musteri-grubu pivotlari, medyasi canonical kayda
     * tasinir; kopya hard-delete edilir. Panelden eklenen yabanci
     * koleksiyonlara dokunulmaz.
     */
    protected function pruneForeignCollections(CollectionGroup $group, Language $language): void
    {
        $collectionMorph = (new ProductCollection)->getMorphClass();
        $pruned = 0;

        foreach ($this->collections as $col) {
            $canonical = $this->findCollectionByName($group->id, $col['name']);

            if (! $canonical) {
                $canonicalId = Url::where('element_type', $collectionMorph)
                    ->where('slug', $col['handle'])
                    ->orderBy('element_id')
                    ->value('element_id');

                $canonical = $canonicalId ? ProductCollection::find($canonicalId) : null;
            }

            if (! $canonical) {
                continue;
            }

            $candidates = ProductCollection::where('collection_group_id', $group->id)
                ->whereKeyNot($canonical->id)
                ->get();

            foreach ($candidates as $duplicate) {
                $dupName = mb_strtolower(trim((string) $duplicate->translateAttribute('name')));
                $isNameCopy = $dupName === mb_strtolower(trim($col['name']));
                $isSlugCopy = Url::where('element_type', $collectionMorph)
                    ->where('element_id', $duplicate->id)
                    ->where('slug', $col['handle'])
                    ->exists();

                if (! $isNameCopy && ! $isSlugCopy) {
                    continue;
                }

                $productIds = DB::table('lunar_collection_product')
                    ->where('collection_id', $duplicate->id)
                    ->pluck('product_id')
                    ->all();

                foreach ($productIds as $productId) {
                    DB::table('lunar_collection_product')->insertOrIgnore([
                        'collection_id' => $canonical->id,
                        'product_id' => $productId,
                        'position' => 9999,
                    ]);
                }

                DB::table('lunar_collection_product')->where('collection_id', $duplicate->id)->delete();

                $canonical->channels()->syncWithoutDetaching($duplicate->channels()->pluck('lunar_channels.id')->all());
                $canonical->customerGroups()->syncWithoutDetaching($duplicate->customerGroups()->pluck('lunar_customer_groups.id')->all());
                $duplicate->channels()->detach();
                $duplicate->customerGroups()->detach();
                $duplicate->brands()->detach();
                $duplicate->discounts()->detach();

                foreach ($duplicate->getMedia('images') as $media) {
                    $existsOnCanonical = $canonical->media()
                        ->where('file_name', $media->file_name)
                        ->exists();

                    if ($existsOnCanonical) {
                        $media->delete();
                    } else {
                        $media->update(['model_id' => $canonical->id]);
                    }
                }

                Url::where('element_type', $collectionMorph)
                    ->where('element_id', $duplicate->id)
                    ->delete();

                $duplicate->delete();
                $pruned++;
            }
        }

        if ($pruned > 0) {
            $this->command->info("  ✓ {$pruned} kopya koleksiyon budandı");
        }
    }

    // ────────────────────────────────────────────────────────────
    // Products
    // ────────────────────────────────────────────────────────────

    /**
     * @param  array<string, ProductCollection>  $collections
     */
    protected function createProducts(
        ProductType $productType,
        Brand $brand,
        TaxClass $taxClass,
        Currency $currency,
        CustomerGroup $customerGroup,
        Channel $channel,
        Language $language,
        array $collections
    ): int {
        $count = 0;

        foreach ($this->products as $p) {
            // Slug ile bak, varsa geç (idempotent)
            // Not: DB'de morph alias saklanır ('product'), FQCN (Product::class) değil!
            // FQCN ile sorgulamak kontrolü her zaman başarısız yapar ve tekrar seed'de
            // ürün kopyaları + slug çakışmaları üretir.
            $exists = Url::where('slug', $p['slug'])
                ->where('element_type', (new Product)->getMorphClass())
                ->first();
            if ($exists) {
                // Zaten seed edilmiş ürünler için eksik görsel/attribute/yorum top-up'ı
                $this->ensureProductExtras((int) $exists->element_id, $p);

                // Mevcut ürünlere de varyasyonları (seçenek + kombinasyon varyantları) uygula.
                $seeded = Product::find($exists->element_id);

                if ($seeded) {
                    $this->applyProductVariations($seeded, $p);
                }

                continue;
            }

            // 1) Product — slug üzerinden deterministik eşleşme (duplicate üretmez).
            $features = $this->productFeatures[$p['slug']] ?? [];
            $description = $this->productDescriptions[$p['slug']] ?? $p['desc'];

            $existingId = Url::where('element_type', (new Product)->getMorphClass())
                ->where('slug', $p['slug'])
                ->value('element_id');

            $product = $existingId ? Product::find($existingId) : null;

            if (! $product) {
                $product = Product::create([
                    'product_type_id' => $productType->id,
                    'brand_id' => $brand->id,
                    'status' => 'published',
                    'attribute_data' => collect([
                        'name' => new Text($p['name']),
                        'description' => new Text($description),
                        'features' => new Text(implode("\n", $features)),
                    ]),
                ]);
            } else {
                $product->update([
                    'status' => 'published',
                    'attribute_data' => collect([
                        'name' => new Text($p['name']),
                        'description' => new Text($description),
                        'features' => new Text(implode("\n", $features)),
                    ]),
                ]);
            }

            // 2) Product Variant — ürün başına tek varyant (duplicate üretmez).
            $variant = ProductVariant::where('product_id', $product->id)->first();

            if (! $variant) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'tax_class_id' => $taxClass->id,
                    'sku' => strtoupper('LUN-'.Str::random(8)),
                    'gtin' => (string) random_int(1000000000000, 9999999999999),
                    'mpn' => 'MPN-'.strtoupper(Str::random(6)),
                    'ean' => (string) random_int(1000000000000, 9999999999999),
                    'unit_quantity' => 1,
                    'min_quantity' => 1,
                    'quantity_increment' => 1,
                    'shippable' => true,
                    'stock' => $p['stock'],
                    'backorder' => 0,
                    'purchasable' => 'always',
                    'weight_value' => $p['weight'],
                    'weight_unit' => 'kg',
                ]);
            } else {
                $variant->update([
                    'stock' => $p['stock'],
                    'weight_value' => $p['weight'],
                ]);
            }

            // 3) Price (TRY, standart müşteri grubu) — varyant başına tek fiyat.
            // Not: Lunar morphMap kullandığı için FQCN değil, alias ('product_variant') gerekli
            Price::updateOrCreate(
                [
                    'currency_id' => $currency->id,
                    'customer_group_id' => $customerGroup->id,
                    'priceable_type' => Relation::getMorphAlias(ProductVariant::class) ?: 'product_variant',
                    'priceable_id' => $variant->id,
                    'min_quantity' => 1,
                ],
                [
                    'price' => $p['price'] * 100,
                    'compare_price' => null,
                ]
            );

            // 4) URL — ürün başına TEK canonical URL (duplicate üretmez):
            // bu ürüne ait eski '-N' kayıtlarını canonical slug'a geri al, diğerlerini sil.
            $canonical = Url::where('element_type', (new Product)->getMorphClass())
                ->where('slug', $p['slug'])
                ->where('element_id', $product->id)
                ->first();

            if (! $canonical) {
                $adopted = Url::where('element_type', (new Product)->getMorphClass())
                    ->where('element_id', $product->id)
                    ->where('slug', 'like', $p['slug'].'-%')
                    ->first();

                if ($adopted) {
                    $adopted->update(['slug' => $p['slug'], 'default' => true]);
                } else {
                    Url::create([
                        'element_type' => (new Product)->getMorphClass(),
                        'element_id' => $product->id,
                        'slug' => $p['slug'],
                        'language_id' => $language->id,
                        'default' => true,
                    ]);
                }

                Url::where('element_type', (new Product)->getMorphClass())
                    ->where('slug', 'like', $p['slug'].'-%')
                    ->delete();
            }

            // 5) Channels + CustomerGroups
            $product->channels()->syncWithoutDetaching([$channel->id]);
            $product->customerGroups()->syncWithoutDetaching([$customerGroup->id]);

            // 6) Collection pivot
            $collection = $collections[$p['collection']];
            DB::table('lunar_collection_product')->insertOrIgnore([
                'collection_id' => $collection->id,
                'product_id' => $product->id,
                'position' => $count + 1,
            ]);

            // 7) Görsel (ana görsel + 2 galeri varyantı)
            $this->attachProductMedia($product, $p);

            // 8) Demo yorumlar
            $this->createProductReviews($product);

            // 9) Varyasyonlar (renk / beden / model) — tanımlı ürünlerde
            // kombinasyon başına bir varyant + fiyat + stok üretir.
            $this->applyProductVariations($product, $p);

            $count++;
        }

        return $count;
    }

    /**
     * "features" ürün attribute'unu idempotent olarak kaydeder.
     * Kayıtlı olması, Filament hub ürün formunda alanın düzenlenebilir olmasını sağlar.
     */
    /**
     * Seeder listesinde olmayan yabancı/duplicate ürünleri budar.
     * Böylece katalog her seed koşusu sonunda tam listedeki ürünlerden oluşur.
     * Sepet/sipariş satırları canonical ürüne yönlendirilir, veri kaybolmaz.
     */
    protected function pruneForeignProducts(array $products, Channel $channel, Currency $currency, CustomerGroup $customerGroup): void
    {
        $validSlugs = collect($products)->pluck('slug')->all();

        $validProductIds = Url::where('element_type', (new Product)->getMorphClass())
            ->whereIn('slug', $validSlugs)
            ->pluck('element_id')
            ->unique()
            ->values()
            ->all();

        $foreignIds = Product::whereNotIn('id', $validProductIds)->pluck('id')->all();

        if (empty($foreignIds)) {
            return;
        }

        // Canonical eşleşme: slug tabanına göre (örn. slim-fit-kot-pantolon-3 → slim-fit-kot-pantolon)
        $canonicalBySlug = Url::where('element_type', (new Product)->getMorphClass())
            ->whereIn('slug', $validSlugs)
            ->get(['slug', 'element_id'])
            ->keyBy('slug');

        $dupCount = 0;

        foreach ($foreignIds as $foreignId) {
            $foreignUrl = Url::where('element_type', (new Product)->getMorphClass())
                ->where('element_id', $foreignId)
                ->first();

            $canonicalId = null;

            if ($foreignUrl) {
                $base = preg_replace('/-\d+$/', '', $foreignUrl->slug);
                $canonicalId = $canonicalBySlug->get($base)?->element_id;
            }

            $canonicalId ??= $validProductIds[0] ?? null;

            if (! $canonicalId) {
                continue;
            }

            $oldVariantIds = ProductVariant::where('product_id', $foreignId)->pluck('id')->all();
            $newVariantId = ProductVariant::where('product_id', $canonicalId)->value('id');

            if ($newVariantId && ! empty($oldVariantIds)) {
                DB::table('lunar_cart_lines')->whereIn('purchasable_id', $oldVariantIds)->update(['purchasable_id' => $newVariantId]);
                DB::table('lunar_order_lines')->whereIn('purchasable_id', $oldVariantIds)->update(['purchasable_id' => $newVariantId]);
            }

            Price::where('priceable_type', (new ProductVariant)->getMorphClass())
                ->whereIn('priceable_id', $oldVariantIds)
                ->delete();
            Url::where('element_type', (new Product)->getMorphClass())
                ->where('element_id', $foreignId)
                ->delete();
            DB::table('media')->where('model_type', 'product')->where('model_id', $foreignId)->delete();
            ProductReview::where('product_id', $foreignId)->delete();
            WishlistItem::where('product_id', $foreignId)->delete();
            DB::table('lunar_collection_product')->where('product_id', $foreignId)->delete();
            ProductVariant::where('product_id', $foreignId)->delete();
            Product::where('id', $foreignId)->delete();

            $dupCount++;
        }

        if ($dupCount > 0) {
            $this->command->info("  ✓ {$dupCount} yabancı/duplicate ürün budandı");
        }
    }

    protected function ensureFeaturesAttribute(): void
    {
        $group = AttributeGroup::firstOrCreate(
            ['attributable_type' => 'product', 'handle' => 'product-details'],
            ['name' => json_encode(['en' => 'Product Details']), 'position' => 1]
        );

        Attribute::firstOrCreate(
            [
                'attribute_type' => 'product',
                'attribute_group_id' => $group->id,
                'handle' => 'features',
            ],
            [
                'position' => 3,
                'name' => json_encode(['en' => 'Features']),
                'type' => Text::class,
                'required' => false,
                'system' => false,
                'searchable' => false,
                'filterable' => false,
                'configuration' => json_encode(['richtext' => false]),
            ]
        );
    }

    /**
     * Ürüne tüm demo görsellerini (ana + 2 varyant) ekler; var olanları tekrar eklemez.
     */
    protected function attachProductMedia(Product $product, array $p): void
    {
        $variants = ['', '-gorsel-2', '-gorsel-3'];
        $added = 0;

        foreach ($variants as $suffix) {
            $imgPath = public_path("images/demo/{$p['slug']}{$suffix}.svg");
            if (! file_exists($imgPath)) {
                continue;
            }

            // Aynı dosya adı zaten bağlıysa atla (idempotent)
            $fileName = basename($imgPath);
            $alreadyAttached = $product->media()
                ->where('file_name', $fileName)
                ->exists();

            if ($alreadyAttached) {
                continue;
            }

            $product->addMedia($imgPath)
                ->preservingOriginal()
                ->usingName($p['name'].($suffix !== '' ? ' '.trim(str_replace('-', ' ', $suffix)) : ''))
                ->toMediaCollection('images');
            $added++;
        }
    }

    /**
     * Ürüne idempotent demo yorumlar ekler (yalnızca hiç yorumu yoksa).
     */
    protected function createProductReviews(Product $product): void
    {
        if (ProductReview::where('product_id', $product->id)->exists()) {
            return;
        }

        $name = (string) ($product->translateAttribute('name') ?? 'Ürün');
        $reviewCount = 2 + ($product->id % 3); // ürün başına 2-4 yorum

        for ($i = 0; $i < $reviewCount; $i++) {
            $templateIndex = ($product->id + $i) % count($this->reviewTemplates);
            $body = str_replace(
                '{name}',
                $name,
                $this->reviewTemplates[$templateIndex]
            );

            ProductReview::create([
                'product_id' => $product->id,
                'user_id' => null,
                'name' => $this->reviewerNames[($product->id + $i) % count($this->reviewerNames)],
                'rating' => $this->reviewRatings[$templateIndex],
                'title' => $this->reviewTitles[$templateIndex],
                'body' => $body,
                'is_approved' => true,
            ]);
        }
    }

    /**
     * Önceden seed edilmiş ürünler için eksik galeri görseli, features/description
     * verisi ve demo yorumları idempotent olarak tamamlar.
     */
    protected function ensureProductExtras(int $productId, array $p): void
    {
        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        // 1) Medya top-up
        $this->attachProductMedia($product, $p);

        // 2) Attribute top-up (yalnızca seeder'ın kendi eski kısa metni varsa günceller;
        //    elle düzenlenmiş açıklamalara dokunmaz)
        $data = $product->attribute_data;
        $currentDesc = (string) ($product->translateAttribute('description') ?? '');

        if ($currentDesc === $p['desc']) {
            $data['description'] = new Text($this->productDescriptions[$p['slug']] ?? $p['desc']);
        }

        if (! isset($data['features']) || blank($product->translateAttribute('features'))) {
            $data['features'] = new Text(implode("\n", $this->productFeatures[$p['slug']] ?? []));
        }

        $product->attribute_data = $data;
        $product->save();

        // 3) Yorum top-up
        $this->createProductReviews($product);
    }

    /**
     * Dil bazlı benzersiz ürün slug'ı üretir; çakışırsa -2, -3... sonekleri dener.
     * (Aynı ürünün kendi mevcut URL kayıtları hariç tutulur.)
     */
    // ────────────────────────────────────────────────────────────
    // Varyasyonlar (renk / beden / model ...)
    // ────────────────────────────────────────────────────────────

    /**
     * Varyasyon tanımı olan ürüne seçenekleri, kombinasyon varyantlarını,
     * fiyat ve stoklarını idempotent biçimde uygular.
     *
     * @param  array<string, mixed>  $p
     */
    protected function applyProductVariations(Product $product, array $p): void
    {
        $plan = $this->variations[$p['slug']] ?? null;

        if (empty($plan)) {
            return;
        }

        $taxClass = TaxClass::where('default', true)->first() ?? TaxClass::first();
        $currency = Currency::where('default', true)->first();
        $customerGroup = CustomerGroup::where('handle', 'default')->first();

        if (! $taxClass || ! $currency || ! $customerGroup) {
            return;
        }

        $options = [];
        $optionPositions = [];
        $position = 1;

        foreach ($plan as $optionHandle => $values) {
            $options[$optionHandle] = $this->ensureProductOption($p['slug'], $optionHandle, $values);
            $optionPositions[$options[$optionHandle]->id] = ['position' => $position];
            $position++;
        }

        $product->productOptions()->sync($optionPositions);

        $combinations = $this->combineVariationValues($plan);
        $perVariantStock = max(1, (int) floor(((int) $p['stock']) / max(1, count($combinations))));

        foreach ($combinations as $index => $combination) {
            $valueIds = [];
            $priceDelta = 0;
            $outOfStock = false;
            $valid = true;

            foreach ($combination as $optionHandle => $value) {
                $matched = $options[$optionHandle]->values->first(
                    fn (ProductOptionValue $optionValue) => $optionValue->translate('name') === $value['name']
                );

                if (! $matched) {
                    $valid = false;

                    break;
                }

                $valueIds[] = $matched->id;

                if (array_key_exists('stock', $value) && (int) $value['stock'] === 0) {
                    $outOfStock = true;
                }

                $priceDelta += (int) ($value['price_delta'] ?? 0);
            }

            if (! $valid) {
                continue;
            }

            $this->upsertVariationVariant(
                $product,
                $p,
                $index,
                $combination,
                $valueIds,
                $priceDelta,
                $outOfStock,
                $perVariantStock,
                $taxClass,
                $currency,
                $customerGroup
            );
        }
    }

    /**
     * Kombinasyon varyantını SKU bazlı, idempotent biçimde oluşturur/günceller.
     *
     * @param  array<string, mixed>  $p
     * @param  array<string, array<string, mixed>>  $combination
     * @param  array<int, int>  $valueIds
     */
    protected function upsertVariationVariant(
        Product $product,
        array $p,
        int $index,
        array $combination,
        array $valueIds,
        int $priceDelta,
        bool $outOfStock,
        int $perVariantStock,
        TaxClass $taxClass,
        Currency $currency,
        CustomerGroup $customerGroup
    ): void {
        $sku = $this->variationSku($p['slug'], $combination);

        $variant = ProductVariant::where('product_id', $product->id)
            ->where('sku', $sku)
            ->first();

        // Ürün daha önce tek varyantlı seed edildiyse ilk kombinasyonda o varyantı
        // benimse; böylece sepet/sipariş satırlarındaki varyant referansı korunur.
        if (! $variant && $index === 0) {
            $existing = ProductVariant::where('product_id', $product->id)->orderBy('id')->get();

            if ($existing->count() === 1 && $existing->first()->values()->count() === 0) {
                $variant = $existing->first();
                $variant->update(['sku' => $sku]);
            }
        }

        $stock = $outOfStock ? 0 : $perVariantStock;

        if (! $variant) {
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'tax_class_id' => $taxClass->id,
                'sku' => $sku,
                'gtin' => (string) random_int(1000000000000, 9999999999999),
                'mpn' => 'MPN-'.strtoupper(Str::random(6)),
                'ean' => (string) random_int(1000000000000, 9999999999999),
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'shippable' => true,
                'stock' => $stock,
                'backorder' => 0,
                'purchasable' => 'always',
                'weight_value' => $p['weight'],
                'weight_unit' => 'kg',
            ]);
        } else {
            $variant->update([
                'stock' => $stock,
                'weight_value' => $p['weight'],
            ]);
        }

        // Seçenek değerleri ile varyantı bağla (vitrindeki filtreleme bunu kullanır).
        $variant->values()->sync($valueIds);

        // Varyant fiyatı = baz fiyat + kombinasyon farkı.
        Price::updateOrCreate(
            [
                'currency_id' => $currency->id,
                'customer_group_id' => $customerGroup->id,
                'priceable_type' => Relation::getMorphAlias(ProductVariant::class) ?: 'product_variant',
                'priceable_id' => $variant->id,
                'min_quantity' => 1,
            ],
            [
                'price' => (((int) $p['price']) + $priceDelta) * 100,
                'compare_price' => null,
            ]
        );
    }

    /**
     * Seçenek değerlerinin kartezyen çarpımını (tüm kombinasyonları) üretir.
     *
     * @param  array<string, array<int, array<string, mixed>>>  $plan
     * @return array<int, array<string, array<string, mixed>>>
     */
    protected function combineVariationValues(array $plan): array
    {
        $combinations = [[]];

        foreach ($plan as $optionHandle => $values) {
            $merged = [];

            foreach ($combinations as $combination) {
                foreach ($values as $value) {
                    $merged[] = $combination + [$optionHandle => $value];
                }
            }

            $combinations = $merged;
        }

        return $combinations;
    }

    /**
     * Kombinasyondan deterministik SKU üretir (tekrar seed'de çoğaltmaz).
     *
     * @param  array<string, array<string, mixed>>  $combination
     */
    protected function variationSku(string $productSlug, array $combination): string
    {
        $suffix = collect($combination)
            ->map(fn (array $value): string => Str::upper(Str::slug((string) $value['name'], '-')))
            ->implode('-');

        return 'LUN-'.Str::upper(Str::slug($productSlug, '-')).'-'.$suffix;
    }

    /**
     * Ürüne özel seçeneği ve değerlerini idempotent biçimde oluşturur.
     *
     * Renk gibi seçeneklerde değerin `meta.hex` alanı doldurulur; vitrin bu
     * değeri renk swatch'ı olarak kullanır.
     *
     * @param  array<int, array<string, mixed>>  $values
     */
    protected function ensureProductOption(string $productSlug, string $optionHandle, array $values): ProductOption
    {
        $handle = $productSlug.'-'.$optionHandle;
        $name = $this->optionLabels[$optionHandle] ?? Str::headline($optionHandle);

        $option = ProductOption::firstOrCreate(
            ['handle' => $handle],
            ['name' => ['tr' => $name, 'en' => $name], 'shared' => false]
        );

        $existing = $option->values()->get();

        foreach ($values as $position => $value) {
            $alreadyThere = $existing->contains(
                fn (ProductOptionValue $optionValue): bool => $optionValue->translate('name') === $value['name']
            );

            if ($alreadyThere) {
                continue;
            }

            $option->values()->create([
                'name' => ['tr' => $value['name'], 'en' => $value['name']],
                'position' => $position + 1,
                'meta' => isset($value['hex']) ? ['hex' => $value['hex']] : null,
            ]);
        }

        return $option->fresh('values');
    }

    protected function uniqueProductSlug(string $base, int $languageId, ?int $ignoreProductId = null): string
    {
        $morph = (new Product)->getMorphClass();
        $slug = $base;
        $i = 1;

        while (Url::where('slug', $slug)
            ->where('language_id', $languageId)
            ->where(function ($query) use ($morph, $ignoreProductId) {
                $query->where('element_type', '!=', $morph)
                    ->orWhere('element_id', '!=', $ignoreProductId);
            })
            ->exists()) {
            $i++;
            $slug = $base.'-'.$i;
        }

        return $slug;
    }

    // ────────────────────────────────────────────────────────────
    // Users & Customers
    // ────────────────────────────────────────────────────────────

    protected function createAdminUser(): void
    {
        $password = env('EXPORTDEPO_ADMIN_PASSWORD');

        if (blank($password)) {
            throw new \RuntimeException('EXPORTDEPO_ADMIN_PASSWORD is required to seed the panel admin.');
        }

        $staff = Staff::updateOrCreate(
            ['email' => 'admin@exportdepo.test'],
            [
                'first_name' => 'ExportDepo',
                'last_name' => 'Admin',
                'admin' => true,
                'password' => $password,
                'email_verified_at' => now(),
            ]
        );

        try {
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'staff']);
            $staff->assignRole($adminRole);
        } catch (\Throwable $e) {
            $this->command->warn('  ! Rol atanamadı (Spatie Permission?): '.$e->getMessage());
        }
    }

    protected function createTestCustomer(CustomerGroup $customerGroup): void
    {
        $password = env('EXPORTDEPO_CUSTOMER_PASSWORD', 'password');

        if (blank($password)) {
            $password = 'password';
        }

        // Seed initial credentials only; never overwrite a customer's chosen password.

        $user = User::firstOrCreate(
            ['email' => 'musteri@test.com'],
            [
                'name' => 'Test Müşteri',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $customer = Customer::where('account_ref', 'TEST-001')->first();
        if (! $customer) {
            $customer = Customer::create([
                'title' => 'Mr',
                'first_name' => 'Test',
                'last_name' => 'Müşteri',
                'account_ref' => 'TEST-001',
                'attribute_data' => collect([
                    'name' => new Text('Test Müşteri'),
                ]),
            ]);

            $customer->customerGroups()->attach($customerGroup->id);
            $customer->users()->attach($user->id);
        }
    }

    /**
     * Eski vitrin hesabı (fresh --seed sonrası kaybolmaması için seeder'a sabitlendi).
     * Şifre .env → EXPORTDEPO_CUSTOMER2_PASSWORD değerinden alınır.
     */
    protected function ensureLegacyCustomer(CustomerGroup $customerGroup): void
    {
        $password = env('EXPORTDEPO_CUSTOMER2_PASSWORD');

        if (blank($password)) {
            return;
        }

        // Preserve existing credentials when demo data is seeded again.
        $user = User::firstOrCreate(
            ['email' => 'mustafaparlak2307@gmail.com'],
            [
                'name' => 'Mustafa Parlak',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $customer = Customer::where('account_ref', 'LEGACY-001')->first();
        if (! $customer) {
            $customer = Customer::create([
                'title' => 'Mr',
                'first_name' => 'Mustafa',
                'last_name' => 'Parlak',
                'account_ref' => 'LEGACY-001',
                'attribute_data' => collect([
                    'name' => new Text('Mustafa Parlak'),
                ]),
            ]);

            $customer->customerGroups()->attach($customerGroup->id);
            $customer->users()->attach($user->id);
        }
    }

    /**
     * Eski panel hesabı (fresh --seed sonrası kaybolmaması için seeder'a sabitlendi).
     * Şifre .env → EXPORTDEPO_ADMIN2_PASSWORD değerinden alınır.
     */
    protected function ensureLegacyStaff(): void
    {
        $password = env('EXPORTDEPO_ADMIN2_PASSWORD');

        if (blank($password)) {
            return;
        }

        $staff = Staff::updateOrCreate(
            ['email' => 'admin@lunar.shop'],
            [
                'first_name' => 'Legacy',
                'last_name' => 'Admin',
                'admin' => true,
                'password' => $password,
                'email_verified_at' => now(),
            ]
        );

        try {
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'staff']);
            $staff->assignRole($adminRole);
        } catch (\Throwable $e) {
            $this->command->warn('  ! Rol atanamadı (Spatie Permission?): '.$e->getMessage());
        }
    }

    /**
     * Demo müşteri için örnek istek listesi kayıtları (idempotent).
     */
    protected function createDemoWishlist(): void
    {
        $user = User::where('email', 'musteri@test.com')->first();

        if (! $user) {
            return;
        }

        $productIds = Product::query()
            ->where('status', 'published')
            ->orderBy('id')
            ->limit(4)
            ->pluck('id')
            ->all();

        foreach ($productIds as $productId) {
            WishlistItem::firstOrCreate([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
        }
    }
}
