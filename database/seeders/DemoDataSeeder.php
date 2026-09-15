<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Collection as ProductCollection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\Url;
use Lunar\Admin\Models\Staff;
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

    /** @var array<int, array<string, string>> */
    protected array $collections = [
        ['handle' => 'elektronik', 'name' => 'Elektronik', 'desc' => 'Bilgisayar, telefon, aksesuar ve diğer teknoloji ürünleri.'],
        ['handle' => 'giyim', 'name' => 'Giyim', 'desc' => 'Kadın, erkek ve unisex giyim ürünleri.'],
        ['handle' => 'ev-yasam', 'name' => 'Ev & Yaşam', 'desc' => 'Ev dekorasyonu, mutfak ve yaşam alanlarınız için ürünler.'],
    ];

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
            $customerGroup = $this->ensureDefaultCustomerGroup();
            $productType = $this->ensureDefaultProductType();
            $collectionGroup = $this->ensureDefaultCollectionGroup();
            $brand = $this->ensureBrand();

            $this->command->info('  ✓ Kanal, para birimi, dil, KDV, marka hazır');

            $collections = $this->createCollections($collectionGroup, $language);
            $this->command->info('  ✓ 3 koleksiyon oluşturuldu');

            $count = $this->createProducts($productType, $brand, $taxClass, $currency, $customerGroup, $channel, $language, $collections);
            $this->command->info("  ✓ {$count} ürün + varyant + fiyat + stok + görsel eklendi");

            $this->createAdminUser();
            $this->command->info('  ✓ Admin kullanıcı oluşturuldu');

            $this->createTestCustomer($customerGroup);
            $this->command->info('  ✓ Test müşteri oluşturuldu');
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

        foreach ($this->products as $p) {
            $color = $colorMap[$p['slug']] ?? '#6b7280';
            $path = $dir . DIRECTORY_SEPARATOR . $p['slug'] . '.svg';
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">'
                . '<defs><linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">'
                . '<stop offset="0%" style="stop-color:' . $color . ';stop-opacity:1"/>'
                . '<stop offset="100%" style="stop-color:rgba(0,0,0,0.4);stop-opacity:1"/>'
                . '</linearGradient></defs>'
                . '<rect width="600" height="600" fill="url(#bg)"/>'
                . '<circle cx="300" cy="240" r="110" fill="rgba(255,255,255,0.15)"/>'
                . '<text x="300" y="280" font-size="40" text-anchor="middle" fill="white" font-family="Arial">' . htmlspecialchars($p['name']) . '</text>'
                . '<text x="300" y="340" font-size="100" text-anchor="middle" fill="rgba(255,255,255,0.5)">📦</text>'
                . '<text x="300" y="450" font-family="Arial" font-size="22" text-anchor="middle" fill="rgba(255,255,255,0.85)">ExportDepo</text>'
                . '</svg>';
            file_put_contents($path, $svg);
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
            ['name' => 'Türkçe', 'default' => true, 'enabled' => true]
        );
    }

    protected function ensureDefaultTaxClass(): TaxClass
    {
        return TaxClass::firstOrCreate(['name' => 'KDV'], ['default' => true]);
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
     * @return array<string, ProductCollection>
     */
    protected function createCollections(CollectionGroup $group, Language $language): array
    {
        $created = [];
        $channel = Channel::where('handle', 'web')->first();
        $cg = CustomerGroup::where('handle', 'default')->first();

        foreach ($this->collections as $col) {
            $collection = ProductCollection::where('handle', $col['handle'])->first();
            if (! $collection) {
                $collection = ProductCollection::create([
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
                    'element_type' => 'collection',
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
                continue;
            }

            // 1) Product
            $product = Product::create([
                'product_type_id' => $productType->id,
                'brand_id' => $brand->id,
                'status' => 'published',
                'attribute_data' => collect([
                    'name' => new Text($p['name']),
                    'description' => new Text($p['desc']),
                ]),
            ]);

            // 2) Product Variant
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'tax_class_id' => $taxClass->id,
                'sku' => strtoupper('LUN-' . Str::random(8)),
                'gtin' => (string) random_int(1000000000000, 9999999999999),
                'mpn' => 'MPN-' . strtoupper(Str::random(6)),
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

            // 3) Price (TRY, standart müşteri grubu) — price kuruş cinsinden
            // Not: Lunar morphMap kullandığı için FQCN değil, alias ('product_variant') gerekli
            Price::create([
                'currency_id' => $currency->id,
                'customer_group_id' => $customerGroup->id,
                'priceable_type' => \Illuminate\Database\Eloquent\Relations\Relation::getMorphAlias(ProductVariant::class) ?: 'product_variant',
                'priceable_id' => $variant->id,
                'price' => $p['price'] * 100,
                'compare_price' => null,
                'min_quantity' => 1,
            ]);

            // 4) URL — dil bazlı benzersiz slug + firstOrCreate (tekrar seed'e dayanıklı)
            $slug = $this->uniqueProductSlug($p['slug'], $language->id, $product->id);
            Url::firstOrCreate(
                [
                    'element_type' => (new Product)->getMorphClass(),
                    'element_id' => $product->id,
                    'language_id' => $language->id,
                ],
                ['slug' => $slug, 'default' => true]
            );

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

            // 7) Görsel
            $imgPath = public_path("images/demo/{$p['slug']}.svg");
            if (file_exists($imgPath)) {
                $product->addMedia($imgPath)
                    ->preservingOriginal()
                    ->usingName($p['name'])
                    ->toMediaCollection('images');
            }

            $count++;
        }

        return $count;
    }

    /**
     * Dil bazlı benzersiz ürün slug'ı üretir; çakışırsa -2, -3... sonekleri dener.
     * (Aynı ürünün kendi mevcut URL kayıtları hariç tutulur.)
     */
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
            $this->command->warn('  ! Rol atanamadı (Spatie Permission?): ' . $e->getMessage());
        }
    }

    protected function createTestCustomer(CustomerGroup $customerGroup): void
    {
        $user = User::firstOrCreate(
            ['email' => 'musteri@test.com'],
            [
                'name' => 'Test Müşteri',
                'password' => Hash::make('password'),
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
}
