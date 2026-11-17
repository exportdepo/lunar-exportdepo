<?php

namespace Tests\Feature;

use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\ProductPage;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Lunar\FieldTypes\Text;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\TaxClass;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Vitrin ürün ve ödeme formlarının erişilebilirlik sözleşmesini doğrular.
 *
 * Denetimler yalnızca "şu metin sayfada var mı" kontrolüyle değil, DOM
 * yapısıyla yapılır. Çünkü bu görevin çekirdek sözleşmesi etiket/kontrol
 * İLİŞKİSİDİR: legend'in fieldset'in ilk çocuğu olması ve denetimlerin o
 * fieldset'in içinde bulunması. Bağımsız metin kontrolleri, bu ilişki
 * bozulduğunda bile yeşil kalırdı.
 */
class StorefrontProductAccessibilityTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    protected function tearDown(): void
    {
        CartSession::forget();

        parent::tearDown();
    }

    public function test_checkout_payment_options_are_grouped_with_a_named_legend(): void
    {
        $fixture = $this->publishedProductWithOptions();

        Livewire::test(HomePage::class)->call('addToCart', $fixture['product']->id);

        config([
            'lunar.payments.types.stripe.secret' => 'sk_test_accessibility',
            'services.stripe.webhook_secret' => 'whsec_accessibility',
        ]);

        $xpath = $this->parseHtml($this->get('/checkout')->assertOk()->getContent());

        $group = $this->firstNode($xpath, '//fieldset[legend[contains(normalize-space(.), "Ödeme Yöntemi")]]');

        $this->assertNotNull($group, 'Ödeme seçenekleri, adlandırılmış bir fieldset içinde bulunmalı.');

        // HTML sözleşmesi: legend, fieldset'in ilk eleman çocuğu olmalı.
        $this->assertSame(
            'legend',
            $this->firstElementChildName($group),
            'Ödeme fieldset\'inin ilk eleman çocuğu legend olmalı.',
        );

        // Radyolar bu fieldset'in İÇİNDE olmalı.
        $this->assertGreaterThanOrEqual(
            2,
            $this->countNodes($xpath, './/input[@type="radio"]', $group),
            'Ödeme radyoları, "Ödeme Yöntemi" adlı fieldset\'in içinde olmalı.',
        );

        // Görünür başlık korunmalı (legend görsel olarak gizli olduğu için).
        $this->assertSame(
            1,
            $this->countNodes($xpath, '//h2[normalize-space(.)="Ödeme"]'),
            'Görünür "Ödeme" başlığı korunmalı.',
        );
    }

    public function test_product_option_groups_render_as_named_fieldsets(): void
    {
        $fixture = $this->publishedProductWithOptions();

        $xpath = $this->parseHtml(
            Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])->html(),
        );

        $outer = $this->firstNode(
            $xpath,
            '//fieldset[legend[contains(normalize-space(.), "Ürün seçenekleri")]]',
        );

        $this->assertNotNull($outer, 'Ürün seçenekleri, "Ürün seçenekleri" adlı bir fieldset içinde olmalı.');
        $this->assertSame(
            'legend',
            $this->firstElementChildName($outer),
            'Dış fieldset\'in ilk eleman çocuğu legend olmalı.',
        );

        foreach (['Renk', 'Beden'] as $groupName) {
            $group = $this->firstNode(
                $xpath,
                './/fieldset[legend[starts-with(normalize-space(.), "'.$groupName.'")]]',
                $outer,
            );

            $this->assertNotNull(
                $group,
                "{$groupName} grubu, kendi adlandırılmış fieldset'ine sahip olmalı.",
            );
            $this->assertSame(
                'legend',
                $this->firstElementChildName($group),
                "{$groupName} fieldset'inin ilk eleman çocuğu legend olmalı.",
            );
            $this->assertGreaterThan(
                0,
                $this->countNodes($xpath, './/button', $group),
                "{$groupName} grubunun düğmeleri fieldset'in içinde olmalı.",
            );
        }
    }

    public function test_colour_swatch_accessible_name_includes_colour_and_hex_value(): void
    {
        $fixture = $this->publishedProductWithOptions();

        $xpath = $this->parseHtml(
            Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])->html(),
        );

        $swatch = $this->firstNode($xpath, '//button[starts-with(@aria-label, "Renk: Siyah")]');

        $this->assertNotNull($swatch, 'Renk düğmesi bulunamadı.');
        $this->assertSame('Renk: Siyah (#111827)', $swatch->getAttribute('aria-label'));
        $this->assertSame(
            'Siyah',
            $swatch->getAttribute('title'),
            'Renk düğmesinin title niteliği değişmemeli (hex eklenmemeli).',
        );

        $outOfStock = $this->firstNode($xpath, '//button[starts-with(@aria-label, "Renk: Beyaz")]');

        $this->assertNotNull($outOfStock, 'Stokta olmayan renk düğmesi bulunamadı.');
        $this->assertSame('Renk: Beyaz (#f1f5f9) (stokta yok)', $outOfStock->getAttribute('aria-label'));
        $this->assertSame(
            'Beyaz (stokta yok)',
            $outOfStock->getAttribute('title'),
            'Renk düğmesinin title niteliği değişmemeli (hex eklenmemeli).',
        );
    }

    public function test_colour_swatch_announces_the_same_hex_value_it_renders(): void
    {
        $fixture = $this->publishedProductWithOptions();

        $xpath = $this->parseHtml(
            Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])->html(),
        );

        $swatches = $xpath->query('//button[starts-with(@aria-label, "Renk: ")]');

        $this->assertGreaterThan(0, $swatches->length, 'Renk düğmesi bulunamadı.');

        foreach ($swatches as $swatch) {
            $label = $swatch->getAttribute('aria-label');

            $this->assertSame(
                1,
                preg_match('/\((#[0-9a-fA-F]{3,8})\)/', $label, $matches),
                "Renk etiketi geçerli bir hex içermeli: {$label}",
            );

            $rendered = $xpath->query('.//span[@data-hex]', $swatch)->item(0)?->getAttribute('data-hex');

            $this->assertSame(
                $matches[1],
                $rendered,
                'Ekran okuyucuya duyurulan hex ile çizilen renk aynı olmalı.',
            );
        }
    }

    public function test_non_colour_option_buttons_are_not_prefixed_with_renk(): void
    {
        $fixture = $this->publishedProductWithOptions();

        $xpath = $this->parseHtml(
            Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])->html(),
        );

        $pill = $this->firstNode($xpath, '//button[normalize-space(.)="S"]');

        $this->assertNotNull($pill, 'Beden düğmesi bulunamadı.');
        $this->assertSame(
            '',
            $pill->getAttribute('aria-label'),
            'Renk olmayan düğmelere "Renk:" ön eki eklenmemeli.',
        );
        $this->assertSame('S', $pill->getAttribute('title'));
    }

    public function test_status_messages_are_announced_from_persistent_live_regions(): void
    {
        $fixture = $this->publishedProductWithOptions();

        // Mesaj gösterilmeden önce de canlı bölge DOM içinde bulunmalı;
        // aksi halde ekran okuyucu duyurmaz (bölge ve içerik aynı anda eklenir).
        $before = $this->parseHtml(
            Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
                ->set('activeTab', 'reviews')
                ->html(),
        );

        $regionsBefore = $this->countNodes($before, '//*[@role="status" and @aria-live="polite"]');

        $this->assertGreaterThan(
            0,
            $regionsBefore,
            'Canlı bölge, mesaj gösterilmeden önce de DOM içinde bulunmalı.',
        );

        $after = $this->parseHtml(
            Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
                ->set('activeTab', 'reviews')
                ->set('added', true)
                ->set('reviewSubmitted', true)
                ->html(),
        );

        $this->assertSame(
            $regionsBefore,
            $this->countNodes($after, '//*[@role="status" and @aria-live="polite"]'),
            'Mesajlar gösterilirken canlı bölge sayısı değişmemeli (bölge kalıcı olmalı).',
        );

        $this->assertTrue(
            $this->regionContains($after, 'Ürün sepete eklendi'),
            '"Ürün sepete eklendi" mesajı canlı bölgenin içinde olmalı.',
        );
        $this->assertTrue(
            $this->regionContains($after, 'Değerlendirmeniz alındı'),
            '"Değerlendirmeniz alındı" mesajı canlı bölgenin içinde olmalı.',
        );
    }

    public function test_product_wishlist_button_includes_product_name(): void
    {
        $fixture = $this->publishedProductWithOptions();

        Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
            ->assertSee('title="İstek listesine ekle: Erişilebilirlik Test Ürünü"', false)
            ->assertSee('aria-label="İstek listesine ekle: Erişilebilirlik Test Ürünü"', false);
    }

    public function test_out_of_stock_product_wishlist_button_includes_product_name(): void
    {
        $fixture = $this->publishedProductWithOptions(stock: 0);

        $xpath = $this->parseHtml(
            Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])->html(),
        );

        $button = $this->firstNode($xpath, '//button[contains(@aria-label, "İstek listesine ekle")]');

        $this->assertNotNull($button, 'Stokta olmayan üründeki istek listesi düğmesi bulunamadı.');
        $this->assertSame(
            'İstek listesine ekle: Erişilebilirlik Test Ürünü',
            $button->getAttribute('aria-label'),
        );
        $this->assertSame(
            'İstek listesine ekle: Erişilebilirlik Test Ürünü',
            $button->getAttribute('title'),
        );
    }

    public function test_product_breadcrumb_marks_the_current_page(): void
    {
        $fixture = $this->publishedProductWithOptions();

        Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
            ->assertSee('<li aria-current="page" class="max-w-full truncate font-medium text-slate-700 dark:text-slate-200">Erişilebilirlik Test Ürünü</li>', false);
    }

    /**
     * @return array{product: Product, slug: string}
     */
    private function publishedProductWithOptions(int $stock = 5): array
    {
        $language = Language::query()->where('default', true)->firstOrFail();
        $currency = Currency::query()->where('default', true)->firstOrFail();
        $channel = Channel::query()->where('default', true)->firstOrFail();
        $customerGroup = CustomerGroup::query()->where('default', true)->firstOrFail();
        $taxClass = TaxClass::factory()->create();
        $slug = 'erisilebilirlik-test-'.Str::ulid();

        $product = Product::factory()->create(['status' => 'published']);
        $product->update([
            'attribute_data' => collect(['name' => new Text('Erişilebilirlik Test Ürünü')]),
        ]);

        $product->channels()->syncWithoutDetaching([$channel->id => ['enabled' => true]]);
        $product->customerGroups()->syncWithoutDetaching([
            $customerGroup->id => ['enabled' => true, 'purchasable' => true],
        ]);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => $slug,
            'default' => true,
        ]);

        $renk = ProductOption::create([
            'handle' => 'renk-'.Str::ulid(),
            'name' => ['tr' => 'Renk', 'en' => 'Renk'],
            'shared' => false,
        ]);
        $siyah = $renk->values()->create([
            'name' => ['tr' => 'Siyah', 'en' => 'Siyah'],
            'position' => 1,
            'meta' => ['hex' => '#111827'],
        ]);
        $renk->values()->create([
            'name' => ['tr' => 'Beyaz', 'en' => 'Beyaz'],
            'position' => 2,
            'meta' => ['hex' => '#f1f5f9'],
        ]);
        // Geçersiz hex: ekran okuyucuya duyurulan değer ile çizilen renk
        // aynı olmalı, yoksa ikisi birbirinden ayrışır.
        $renk->values()->create([
            'name' => ['tr' => 'Gri', 'en' => 'Gri'],
            'position' => 3,
            'meta' => ['hex' => ' '],
        ]);

        $beden = ProductOption::create([
            'handle' => 'beden-'.Str::ulid(),
            'name' => ['tr' => 'Beden', 'en' => 'Beden'],
            'shared' => false,
        ]);
        $small = $beden->values()->create([
            'name' => ['tr' => 'S', 'en' => 'S'],
            'position' => 1,
        ]);

        $product->productOptions()->sync([
            $renk->id => ['position' => 1],
            $beden->id => ['position' => 2],
        ]);

        $variant = $product->variants()->create([
            'tax_class_id' => $taxClass->id,
            'sku' => 'ACCESSIBILITY-'.Str::ulid(),
            'stock' => $stock,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'always',
        ]);
        $variant->values()->sync([$siyah->id, $small->id]);
        $variant->prices()->create([
            'currency_id' => $currency->id,
            'price' => 500,
            'min_quantity' => 1,
        ]);

        return [
            'product' => $product,
            'slug' => $slug,
        ];
    }

    private function parseHtml(string $html): DOMXPath
    {
        $document = new DOMDocument;

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function firstNode(DOMXPath $xpath, string $query, ?DOMElement $context = null): ?DOMElement
    {
        $nodes = $xpath->query($query, $context);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private function countNodes(DOMXPath $xpath, string $query, ?DOMElement $context = null): int
    {
        $nodes = $xpath->query($query, $context);

        return $nodes === false ? 0 : $nodes->length;
    }

    /**
     * Bir öğenin ilk eleman çocuğunun etiket adı.
     */
    private function firstElementChildName(DOMElement $element): ?string
    {
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement) {
                return $child->nodeName;
            }
        }

        return null;
    }

    /**
     * Verilen metni içeren bir canlı bölge var mı?
     */
    private function regionContains(DOMXPath $xpath, string $text): bool
    {
        $regions = $xpath->query('//*[@role="status" and @aria-live="polite"]');

        if ($regions === false) {
            return false;
        }

        foreach ($regions as $region) {
            if (str_contains($region->textContent, $text)) {
                return true;
            }
        }

        return false;
    }
}
