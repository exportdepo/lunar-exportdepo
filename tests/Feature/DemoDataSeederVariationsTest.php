<?php

namespace Tests\Feature;

use App\Livewire\Storefront\ProductPage;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Lunar\Models\Collection as ProductCollection;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductVariant;
use Lunar\Models\Url;
use Tests\TestCase;

/**
 * Demo verisinin renk / beden / model gibi varyasyonları doğru ürettiğini doğrular.
 */
class DemoDataSeederVariationsTest extends TestCase
{
    public function test_seeder_creates_colour_and_size_variations_for_apparel(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('premium-pamuk-tisort');

        $this->assertNotNull($product, 'Varyasyonlu ürün seed edilmemiş.');
        $this->assertSame(2, $product->productOptions()->count(), 'Renk ve Beden seçeneği bekleniyordu.');

        // 4 renk × 5 beden = 20 kombinasyon varyantı.
        $this->assertSame(20, $product->variants()->count());

        // Renk seçeneğinin değerleri vitrin swatch'ı için hex kodu taşır.
        $renk = $product->productOptions()->where('handle', 'premium-pamuk-tisort-renk')->first();
        $this->assertNotNull($renk);
        $this->assertSame('Renk', $renk->translate('name'));
        $this->assertSame(4, $renk->values()->count());
        $this->assertSame('#111827', $renk->values()->first()->meta['hex']);

        // Bedenler sıralı ve doğru adlarla üretilir.
        $beden = $product->productOptions()->where('handle', 'premium-pamuk-tisort-beden')->first();
        $this->assertNotNull($beden);
        $this->assertSame(
            ['S', 'M', 'L', 'XL', '2XL'],
            $beden->values()->orderBy('position')->get()->map(fn (ProductOptionValue $value) => $value->translate('name'))->all()
        );

        // Her varyant bir renk ve bir beden değerine bağlıdır.
        foreach ($product->variants as $variant) {
            $this->assertSame(2, $variant->values()->count());
            $this->assertNotNull($variant->prices()->first(), 'Her varyantın fiyatı olmalı.');
        }
    }

    public function test_seeder_applies_price_delta_and_marks_out_of_stock_combinations(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('premium-pamuk-tisort');

        // Baz fiyat 349 TL; Siyah/S fark yok, Siyah/XL +10 TL.
        $base = $product->variants()->where('sku', 'LUN-PREMIUM-PAMUK-TISORT-SIYAH-S')->first();
        $xl = $product->variants()->where('sku', 'LUN-PREMIUM-PAMUK-TISORT-SIYAH-XL')->first();
        $outOfStock = $product->variants()->where('sku', 'LUN-PREMIUM-PAMUK-TISORT-SIYAH-2XL')->first();

        $this->assertNotNull($base);
        $this->assertNotNull($xl);
        $this->assertNotNull($outOfStock);

        $this->assertSame(34900, $base->prices()->first()->price->value);
        $this->assertSame(35900, $xl->prices()->first()->price->value);

        // 2XL kombinasyonu stok dışı işaretlenir; vitrinde soluk/çizili görünür.
        $this->assertSame(0, $outOfStock->stock);
    }

    public function test_seeder_variations_are_idempotent(): void
    {
        $this->seed(DemoDataSeeder::class);

        $variantCount = ProductVariant::count();
        $optionCount = ProductOption::count();
        $valueCount = ProductOptionValue::count();

        $this->seed(DemoDataSeeder::class);

        $this->assertSame($variantCount, ProductVariant::count(), 'Tekrar seed varyant çoğaltmamalı.');
        $this->assertSame($optionCount, ProductOption::count(), 'Tekrar seed seçenek çoğaltmamalı.');
        $this->assertSame($valueCount, ProductOptionValue::count(), 'Tekrar seed seçenek değeri çoğaltmamalı.');
    }

    public function test_seeder_leaves_simple_products_with_a_single_variant(): void
    {
        $this->seed(DemoDataSeeder::class);

        $product = $this->productBySlug('lunarbook-pro-14');

        $this->assertNotNull($product);
        $this->assertSame(0, $product->productOptions()->count());
        $this->assertSame(1, $product->variants()->count());
    }

    public function test_seeder_attaches_real_photos_instead_of_svg_placeholders(): void
    {
        $this->seed(DemoDataSeeder::class);

        $slugs = [
            'lunarbook-pro-14', 'kablosuz-mouse', 'mekanik-klavye', 'bluetooth-kulaklik', 'akilli-saat',
            'premium-pamuk-tisort', 'yaz-elbisesi', 'slim-fit-kot', 'spor-ayakkabi', 'yun-kazak',
            'otomatik-kahve-makinesi', 'seramik-tava-seti', 'bel-yastigi', 'led-masa-lambasi', 'seramik-saksi',
        ];

        foreach ($slugs as $slug) {
            $product = $this->productBySlug($slug);
            $this->assertNotNull($product, "{$slug} ürünu seed edilmemiş.");

            // Galeri 3 gerçek fotoğraftan oluşur (thumbnail şeridi için şart).
            foreach (['', '-gorsel-2', '-gorsel-3'] as $suffix) {
                $this->assertFileExists(
                    public_path("images/demo/photos/{$slug}{$suffix}.jpg"),
                    "{$slug} için photos/ varyant fotoğrafı olmalı."
                );
                $this->assertContains(
                    "{$slug}{$suffix}.jpg",
                    $product->media()->pluck('file_name')->all(),
                    "{$slug} için photos/{$slug}{$suffix}.jpg medyası eklenmemiş."
                );
            }

            // Placeholder medya kalmamış olmalı.
            $this->assertNotContains(
                "{$slug}.svg",
                $product->media()->pluck('file_name')->all(),
                "{$slug} için SVG placeholder medyası kaldırılmamış."
            );
        }
    }

    public function test_seeder_photos_meet_minimum_resolution(): void
    {
        $this->seed(DemoDataSeeder::class);

        $slugs = [
            'lunarbook-pro-14', 'kablosuz-mouse', 'mekanik-klavye', 'bluetooth-kulaklik', 'akilli-saat',
            'premium-pamuk-tisort', 'yaz-elbisesi', 'slim-fit-kot', 'spor-ayakkabi', 'yun-kazak',
            'otomatik-kahve-makinesi', 'seramik-tava-seti', 'bel-yastigi', 'led-masa-lambasi', 'seramik-saksi',
        ];

        foreach ($slugs as $slug) {
            // Ana görsel PDP galerisinde büyük gösterilir: en az 1000px olmalı.
            $main = getimagesize(public_path("images/demo/photos/{$slug}.jpg"));
            $this->assertGreaterThanOrEqual(
                1000,
                $main[0],
                "{$slug} ana fotoğrafı en az 1000px genişliğinde olmalı (ölçülen: {$main[0]}px)."
            );

            // Thumbnail varyantları en az 800px olmalı.
            foreach (['-gorsel-2', '-gorsel-3'] as $suffix) {
                $variant = getimagesize(public_path("images/demo/photos/{$slug}{$suffix}.jpg"));
                $this->assertGreaterThanOrEqual(
                    800,
                    $variant[0],
                    "{$slug}{$suffix} fotoğrafı en az 800px genişliğinde olmalı (ölçülen: {$variant[0]}px)."
                );
            }

            // Vitrine servis edilen storage kopyası da güncel çözünürlükte olmalı
            // (kaynak dosya büyütülüp yeniden seed edilmezse mağaza bulanık kalır).
            $media = $this->productBySlug($slug)->media()->where('file_name', "{$slug}.jpg")->first();
            $this->assertNotNull($media, "{$slug} için ana medya kaydı bulunamadı.");

            $storedPath = $media->getPath();
            $this->assertFileExists($storedPath, "{$slug} için storage kopyası bulunamadı.");

            $stored = getimagesize($storedPath);
            $this->assertNotFalse($stored, "{$slug} için medya dosyası okunamadı.");
            $this->assertGreaterThanOrEqual(
                1000,
                $stored[0],
                "{$slug} için storage'daki ana görsel düşük çözünürlüklü (ölçülen: {$stored[0]}px)."
            );
        }
    }

    public function test_product_page_gallery_exposes_three_images_for_thumbnails(): void
    {
        $this->seed(DemoDataSeeder::class);

        $component = Livewire::test(ProductPage::class, ['slug' => 'premium-pamuk-tisort']);

        $images = array_filter($component->get('images'), fn ($image) => ! empty($image['url']));

        // Blade thumbnail şeridini count($images) > 1 iken gösterir.
        $this->assertGreaterThanOrEqual(3, count($images), 'PDP galerisi thumbnail için en az 3 görsel sunmalı.');
    }

    public function test_seeder_attaches_photo_covers_to_collections(): void
    {
        $this->seed(DemoDataSeeder::class);

        $collectionMorph = (new ProductCollection)->getMorphClass();

        foreach (['elektronik', 'giyim', 'ev-yasam'] as $handle) {
            $this->assertFileExists(
                public_path("images/demo/photos/koleksiyon-{$handle}.jpg"),
                "{$handle} koleksiyonu için kapak fotoğrafı olmalı."
            );

            $collectionId = Url::where('element_type', $collectionMorph)
                ->where('slug', $handle)
                ->value('element_id');

            $this->assertNotNull($collectionId, "{$handle} koleksiyonu seed edilmemiş.");

            $collection = ProductCollection::find($collectionId);
            $this->assertContains(
                "koleksiyon-{$handle}.jpg",
                $collection->media()->pluck('file_name')->all(),
                "{$handle} koleksiyonu için JPG kapak medyası eklenmemiş."
            );
        }
    }

    public function test_seeder_media_is_idempotent_across_runs(): void
    {
        $this->seed(DemoDataSeeder::class);

        $mediaCount = DB::table('media')->count();

        $this->seed(DemoDataSeeder::class);

        $this->assertSame($mediaCount, DB::table('media')->count(), 'Tekrar seed medya çoğaltmamalı.');
    }

    private function productBySlug(string $slug): ?Product
    {
        return Product::whereHas('urls', fn ($query) => $query->where('slug', $slug))->first();
    }
}
