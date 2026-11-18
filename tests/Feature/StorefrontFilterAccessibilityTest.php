<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CollectionPage;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Livewire\Livewire;
use Lunar\FieldTypes\Text;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\Tag;
use Lunar\Models\TaxClass;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Koleksiyon (PLP) filtre kenar çubuğunun başlık yapısını doğrular.
 *
 * Kapsam: filtre paneli bir `<h2>` başlığı taşır ve her filtre grubu
 * (akordeon) kendi `<summary>` etiketi içinde bir `<h3>` başlığı ile
 * tanımlanır. Böylece ekran okuyucu kullanıcısı filtre gruplarını başlık
 * navigasyonuyla gezebilir (WCAG 1.3.1 / 2.4.6).
 */
class StorefrontFilterAccessibilityTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    public function test_filter_sidebar_has_a_level_two_heading(): void
    {
        $xpath = $this->parseHtml($this->collectionHtml());

        $heading = $this->firstNode($xpath, '//aside[@id="plp-filters"]//h2');

        $this->assertNotNull($heading, 'Filtre paneli bir <h2> başlığı taşımalı.');
        $this->assertSame('Filtreler', trim($heading->textContent));
    }

    public function test_every_filter_group_heading_is_wrapped_in_a_heading(): void
    {
        $xpath = $this->parseHtml($this->collectionHtml());

        $summaries = $xpath->query('//aside[@id="plp-filters"]//summary');

        $this->assertGreaterThan(
            0,
            $summaries->length,
            'Test fikstüründe akordeon filtre grupları oluşmadı.',
        );

        // Fikstürdeki gruplar: marka, etiket ve varyant seçeneği (Renk).
        $expectedGroups = ['Marka', 'Etiketler', 'Renk'];

        $foundGroups = [];

        foreach ($summaries as $summary) {
            $groupName = trim(preg_replace('/\s+/', ' ', $summary->textContent) ?? '');

            $heading = $this->firstNode($xpath, './/h3', $summary);

            $this->assertNotNull(
                $heading,
                '"'.$groupName.'" filtresinin <summary> etiketi bir <h3> başlığı içermeli.',
            );
            $this->assertSame(
                $groupName,
                trim($heading->textContent),
                'Başlık metni, filtre grubunun adıyla aynı olmalı.',
            );

            $foundGroups[] = trim($heading->textContent);
        }

        $this->assertSame(
            $expectedGroups,
            $foundGroups,
            'Her filtre grubu kendi adıyla bir başlık olarak sunulmalı.',
        );
    }

    public function test_filter_sidebar_heading_levels_do_not_skip(): void
    {
        $xpath = $this->parseHtml($this->collectionHtml());

        $headings = $xpath->query('//aside[@id="plp-filters"]//h1 | //aside[@id="plp-filters"]//h2 | //aside[@id="plp-filters"]//h3 | //aside[@id="plp-filters"]//h4 | //aside[@id="plp-filters"]//h5 | //aside[@id="plp-filters"]//h6');

        $this->assertGreaterThan(0, $headings->length, 'Filtre panelinde başlık bulunamadı.');

        $levels = [];

        foreach ($headings as $heading) {
            $levels[] = (int) substr($heading->nodeName, 1);
        }

        // İlk başlık <h2> olmalı, sonrası yalnızca <h3> (seviye atlması yok).
        $this->assertSame(2, $levels[0], 'Filtre panelinin ilk başlığı <h2> olmalı.');
        $this->assertGreaterThan(
            1,
            count($levels),
            'Filtre panelinde <h2> dışında en az bir grup başlığı olmalı.',
        );

        foreach (array_slice($levels, 1) as $level) {
            $this->assertSame(
                3,
                $level,
                'Filtre panelindeki alt başlıklar <h3> olmalı (başlık seviyesi atlanmamalı).',
            );
        }
    }

    /**
     * Marka, etiket ve varyant seçeneği içeren bir koleksiyonun HTML çıktısı.
     */
    private function collectionHtml(): string
    {
        $language = Language::query()->where('default', true)->firstOrFail();
        $currency = Currency::query()->where('default', true)->firstOrFail();

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => 'filtre-koleksiyonu',
            'default' => true,
        ]);

        $brand = Brand::create(['name' => 'Test Marka']);
        $tag = Tag::firstOrCreate(['value' => 'TEST ETIKETI']);

        $option = ProductOption::create([
            'handle' => 'renk-'.uniqid(),
            'name' => ['tr' => 'Renk', 'en' => 'Color'],
        ]);
        $optionValue = $option->values()->create([
            'name' => ['tr' => 'Siyah', 'en' => 'Black'],
            'position' => 1,
        ]);

        $product = Product::factory()->create(['status' => 'published']);
        $product->update([
            'attribute_data' => collect(['name' => new Text('Filtre Ürünü')]),
            'brand_id' => $brand->id,
        ]);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => 'filtre-urunu',
            'default' => true,
        ]);
        $product->tags()->sync([$tag->id]);
        $collection->products()->attach($product);

        $variant = $product->variants()->create([
            'tax_class_id' => TaxClass::factory()->create()->id,
            'sku' => 'FIL-'.strtoupper(uniqid()),
            'stock' => 5,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'always',
        ]);
        $variant->values()->sync([$optionValue->id]);
        $variant->prices()->create([
            'currency_id' => $currency->id,
            'price' => 100,
            'min_quantity' => 1,
        ]);

        return Livewire::test(CollectionPage::class, ['slug' => 'filtre-koleksiyonu'])->html();
    }

    private function parseHtml(string $html): DOMXPath
    {
        $document = new DOMDocument;

        // Alpine'ın `@` ile başlayan nitelikleri HTML ayrıştırıcı tarafından
        // düşürülür; denetim yapılabilsin diye `data-a11y-` önekiyle
        // yeniden adlandırılır.
        $html = preg_replace('/(\s)@([a-zA-Z][\w.:-]*)=/', '$1data-a11y-$2=', $html) ?? $html;

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function firstNode(DOMXPath $xpath, string $query, ?DOMElement $context = null): ?DOMElement
    {
        $nodes = $context ? $xpath->query($query, $context) : $xpath->query($query);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }
}
