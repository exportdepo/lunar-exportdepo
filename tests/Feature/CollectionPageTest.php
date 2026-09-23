<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CollectionPage;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Livewire;
use Lunar\Models\Attribute;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\Tag;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;
use Lunar\FieldTypes\Text;
use ReflectionProperty;
use Tests\TestCase;

class CollectionPageTest extends TestCase
{
    public function test_can_search_products_in_collection(): void
    {
        $language = Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => 'test-collection',
            'default' => true,
        ]);
        
        $product1 = Product::factory()->create(['status' => 'published']);
        $product1->update([
            'attribute_data' => collect([
                'name' => new Text('Apple iPhone'),
            ]),
        ]);
        $collection->products()->attach($product1);
        $product1->urls()->create([
            'language_id' => $language->id,
            'slug' => 'iphone',
            'default' => true,
        ]);

        $product2 = Product::factory()->create(['status' => 'published']);
        $product2->update([
            'attribute_data' => collect([
                'name' => new Text('Samsung Galaxy'),
            ]),
        ]);
        $collection->products()->attach($product2);
        $product2->urls()->create([
            'language_id' => $language->id,
            'slug' => 'galaxy',
            'default' => true,
        ]);

        Livewire::test(CollectionPage::class, ['slug' => 'test-collection'])
            ->assertCount('products', 2)
            ->set('searchTerm', 'iPhone')
            ->assertCount('products', 1)
            ->assertSee('Apple iPhone')
            ->assertDontSee('Samsung Galaxy');
    }

    public function test_search_matches_turkish_characters_case_insensitively(): void
    {
        $language = Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => 'test-collection-tr',
            'default' => true,
        ]);

        $product1 = Product::factory()->create(['status' => 'published']);
        $product1->update([
            'attribute_data' => collect([
                'name' => new Text('Bluetooth Kulaklık'),
            ]),
        ]);
        $collection->products()->attach($product1);
        $product1->urls()->create([
            'language_id' => $language->id,
            'slug' => 'kulaklik',
            'default' => true,
        ]);

        $product2 = Product::factory()->create(['status' => 'published']);
        $product2->update([
            'attribute_data' => collect([
                'name' => new Text('Masa Lambası'),
            ]),
        ]);
        $collection->products()->attach($product2);
        $product2->urls()->create([
            'language_id' => $language->id,
            'slug' => 'lambasi',
            'default' => true,
        ]);

        Livewire::test(CollectionPage::class, ['slug' => 'test-collection-tr'])
            ->assertCount('products', 2)
            ->set('searchTerm', 'kulaklık')
            ->assertCount('products', 1)
            ->assertSee('Bluetooth Kulaklık')
            ->assertDontSee('Masa Lambası')
            ->set('searchTerm', 'KULAKLIK')
            ->assertCount('products', 1)
            ->assertSee('Bluetooth Kulaklık');
    }

    /**
     * P0: Varyant filtresi Lunar ProductOptionValue üzerinden çalışmalıdır.
     * Filtre değeri value ID'dir; ad (Siyah/Beyaz) `translate()` ile gelir.
     */
    public function test_variant_option_value_filter_filters_products(): void
    {
        $fx = $this->variantOptionCollection('opt-filter-collection');

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->assertCount('products', 2)
            ->assertCount('options', 1)
            ->assertSet('options', fn (array $options): bool => $options[0]['key'] === 'renk'
                && $options[0]['name'] === 'Renk'
                && count($options[0]['values']) === 2)
            ->set('optionFilters', ['renk' => ['siyah' => true]])
            ->assertCount('products', 1)
            ->assertSee('Siyah Gömlek')
            ->assertDontSee('Beyaz Gömlek');
    }

    /**
     * Checkbox kaldırıldığında (false) ilgili değer optionFilters'tan atılmalı,
     * filtre kalkıp tüm ürünler listeye dönmelidir.
     */
    public function test_cleared_option_filter_entry_is_removed(): void
    {
        $fx = $this->variantOptionCollection('opt-clear-value-collection');

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->set('optionFilters', ['renk' => ['beyaz' => true]])
            ->assertCount('products', 1)
            ->assertSet('optionFilters', ['renk' => ['beyaz' => true]])
            ->set('optionFilters.renk.beyaz', false)
            ->assertCount('products', 2)
            ->assertSet('optionFilters', []);
    }

    // ────────────────────────────────────────────────────────────
    // Fixtures
    // ────────────────────────────────────────────────────────────

    /**
     * Renk seçeneğine sahip iki ürünlü koleksiyon kurar:
     * "Siyah Gömlek" (Siyah value) ve "Beyaz Gömlek" (Beyaz value).
     *
     * `$withSize` true ise Beden seçeneği (S, M) ve üçüncü ürün
     * "Siyah Tişört" (Siyah + S) eklenir: Siyah Gömlek→M, Beyaz Gömlek→M.
     *
     * @return array{collection: Collection, option: ProductOption, siyah: \Lunar\Models\ProductOptionValue, beyaz: \Lunar\Models\ProductOptionValue, beden: ?ProductOption, small: ?\Lunar\Models\ProductOptionValue, medium: ?\Lunar\Models\ProductOptionValue, products: array<string, Product>, slug: string}
     */
    private function variantOptionCollection(string $slug, bool $withSize = false): array
    {
        $context = $this->storefrontContext();
        $language = $context['language'];

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => $slug,
            'default' => true,
        ]);

        $option = ProductOption::create([
            'handle' => 'renk-'.uniqid(),
            'name' => ['tr' => 'Renk', 'en' => 'Color'],
        ]);
        $siyah = $option->values()->create(['name' => ['tr' => 'Siyah', 'en' => 'Black'], 'position' => 1]);
        $beyaz = $option->values()->create(['name' => ['tr' => 'Beyaz', 'en' => 'White'], 'position' => 2]);

        $beden = null;
        $small = null;
        $medium = null;
        if ($withSize) {
            $beden = ProductOption::create([
                'handle' => 'beden-'.uniqid(),
                'name' => ['tr' => 'Beden', 'en' => 'Size'],
            ]);
            $small = $beden->values()->create(['name' => ['tr' => 'S', 'en' => 'S'], 'position' => 1]);
            $medium = $beden->values()->create(['name' => ['tr' => 'M', 'en' => 'M'], 'position' => 2]);
        }

        $valueBySlug = ['siyah-gomlek' => $siyah, 'beyaz-gomlek' => $beyaz];
        $nameBySlug = ['siyah-gomlek' => 'Siyah Gömlek', 'beyaz-gomlek' => 'Beyaz Gömlek'];
        if ($withSize) {
            $valueBySlug['siyah-tisort'] = $siyah;
            $nameBySlug['siyah-tisort'] = 'Siyah Tişört';
        }
        $products = [];

        foreach ($valueBySlug as $productSlug => $value) {
            $product = Product::factory()->create(['status' => 'published']);
            $product->update([
                'attribute_data' => collect(['name' => new Text($nameBySlug[$productSlug])]),
            ]);
            $product->urls()->create([
                'language_id' => $language->id,
                'slug' => $productSlug,
                'default' => true,
            ]);
            $collection->products()->attach($product);

            $variant = $product->variants()->create([
                'tax_class_id' => TaxClass::factory()->create()->id,
                'sku' => 'OPT-'.strtoupper($productSlug).'-'.uniqid(),
                'stock' => 5,
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'shippable' => true,
                'purchasable' => 'always',
            ]);
            $variant->values()->sync([$value->id]);
            $variant->prices()->create([
                'currency_id' => $context['currency']->id,
                'price' => 100,
                'min_quantity' => 1,
            ]);

            $products[$productSlug] = $product;
        }

        if ($withSize) {
            $products['siyah-gomlek']->variants->first()->values()->sync([$siyah->id, $medium->id]);
            $products['beyaz-gomlek']->variants->first()->values()->sync([$beyaz->id, $medium->id]);
            $products['siyah-tisort']->variants->first()->values()->sync([$siyah->id, $small->id]);
        }

        return [
            'collection' => $collection,
            'option' => $option,
            'siyah' => $siyah,
            'beyaz' => $beyaz,
            'beden' => $beden,
            'small' => $small,
            'medium' => $medium,
            'products' => $products,
            'slug' => $slug,
        ];
    }

    /**
     * Seeder davranışını taklit eder: her ürün kendi "Renk" ProductOption'una
     * sahiptir (ayrı handle, aynı "Renk" adı). İki ürün:
     * "Siyah Gömlek" (Siyah+Beyaz) ve "Siyah Tişört" (Siyah+Kırmızı).
     *
     * @return array{collection: Collection, options: array<string, ProductOption>, slug: string}
     */
    private function perProductOptionCollection(string $slug): array
    {
        $context = $this->storefrontContext();
        $language = $context['language'];

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => $slug,
            'default' => true,
        ]);

        $plans = [
            'siyah-gomlek' => ['name' => 'Siyah Gömlek', 'values' => ['Siyah', 'Beyaz']],
            'siyah-tisort' => ['name' => 'Siyah Tişört', 'values' => ['Siyah', 'Kırmızı']],
        ];
        $options = [];

        foreach ($plans as $productSlug => $plan) {
            $option = ProductOption::create([
                'handle' => $productSlug.'-renk-'.uniqid(),
                'name' => ['tr' => 'Renk', 'en' => 'Color'],
            ]);
            foreach ($plan['values'] as $position => $valueName) {
                $option->values()->create(['name' => ['tr' => $valueName, 'en' => $valueName], 'position' => $position + 1]);
            }
            $options[$productSlug] = $option;

            $product = Product::factory()->create(['status' => 'published']);
            $product->update([
                'attribute_data' => collect(['name' => new Text($plan['name'])]),
            ]);
            $product->urls()->create([
                'language_id' => $language->id,
                'slug' => $productSlug,
                'default' => true,
            ]);
            $collection->products()->attach($product);

            // Her değer için bir varyant (seeder kombinasyon yaklaşımı).
            $optionValues = $option->values()->get()
                ->keyBy(fn ($value) => $value->translate('name'));

            foreach ($plan['values'] as $valueName) {
                $variant = $product->variants()->create([
                    'tax_class_id' => TaxClass::factory()->create()->id,
                    'sku' => 'MROPT-'.strtoupper($productSlug).'-'.strtoupper(Str::slug($valueName)).'-'.uniqid(),
                    'stock' => 5,
                    'unit_quantity' => 1,
                    'min_quantity' => 1,
                    'quantity_increment' => 1,
                    'shippable' => true,
                    'purchasable' => 'always',
                ]);
                $variant->values()->sync([$optionValues[$valueName]->id]);
                $variant->prices()->create([
                    'currency_id' => $context['currency']->id,
                    'price' => 100,
                    'min_quantity' => 1,
                ]);
            }
        }

        return ['collection' => $collection, 'options' => $options, 'slug' => $slug];
    }

    /**
     * filterable 'Materyal' özniteliği ekler: Siyah Gömlek→Pamuk, Beyaz Gömlek→Keten.
     *
     * @return array{attribute: Attribute, handle: string}
     */
    private function attachMaterialAttribute(array $fx): array
    {
        $attribute = Attribute::factory()->create([
            'attribute_type' => 'product',
            'handle' => 'material-'.uniqid(),
            'name' => ['tr' => 'Materyal', 'en' => 'Material'],
            'filterable' => true,
        ]);

        $fx['products']['siyah-gomlek']->update([
            'attribute_data' => collect([
                'name' => new Text('Siyah Gömlek'),
                $attribute->handle => new Text('Pamuk'),
            ]),
        ]);
        $fx['products']['beyaz-gomlek']->update([
            'attribute_data' => collect([
                'name' => new Text('Beyaz Gömlek'),
                $attribute->handle => new Text('Keten'),
            ]),
        ]);

        return ['attribute' => $attribute, 'handle' => (string) $attribute->handle];
    }

    private function storefrontContext(): array
    {
        TaxZone::query()->where('default', true)->exists() || TaxZone::factory()->create();

        $language = Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        Currency::query()->where('default', true)->update(['default' => false]);
        $currency = Currency::factory()->create([
            'code' => 'TRY'.uniqid(),
            'default' => true,
        ]);

        return ['language' => $language, 'currency' => $currency];
    }

    // ────────────────────────────────────────────────────────────
    // Marka (P1) ve genel filtre davranışları
    // ────────────────────────────────────────────────────────────

    /**
     * Marka filtresi çoklu seçimdir — seçilen markaların ürünleri (OR),
     * lunar_products.brand_id üzerinden güvenilir SQL yapar.
     */
    public function test_brand_filter_filters_products_by_brand(): void
    {
        $fx = $this->variantOptionCollection('brand-filter-collection');
        $lunar = Brand::create(['name' => 'Lunar '.uniqid()]);
        $aurora = Brand::create(['name' => 'Aurora '.uniqid()]);
        $fx['products']['siyah-gomlek']->update(['brand_id' => $lunar->id]);
        $fx['products']['beyaz-gomlek']->update(['brand_id' => $aurora->id]);

        $lunarKey = Str::slug($lunar->name);
        $auroraKey = Str::slug($aurora->name);

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->assertCount('products', 2)
            ->assertCount('brands', 2)
            ->assertSet('brands', fn (array $brands): bool => collect($brands)->contains(
                fn (array $brand): bool => $brand['key'] === $lunarKey && $brand['id'] === $lunar->id
            ))
            ->set('brandFilters', [$lunarKey => true])
            ->assertCount('products', 1)
            ->assertSee('Siyah Gömlek')
            ->assertDontSee('Beyaz Gömlek')
            // Çoklu seçim (OR): her iki marka → iki ürün de
            ->set('brandFilters.'.$auroraKey, true)
            ->assertSet('brandFilters', [$lunarKey => true, $auroraKey => true])
            ->assertCount('products', 2)
            ->assertSee('Siyah Gömlek')
            ->assertSee('Beyaz Gömlek')
            // Checkbox kaldırma → yalnız Aurora
            ->set('brandFilters.'.$lunarKey, false)
            ->assertCount('products', 1)
            ->assertSee('Beyaz Gömlek')
            ->assertDontSee('Siyah Gömlek');
    }

    /**
     * Etiket filtresi çoklu seçimdir — seçilen etiketlerden herhangi birine
     * sahip ürünler (OR), lunar_tags (morph taggables) üzerinden çalışır.
     */
    public function test_tag_filter_filters_products_by_tag(): void
    {
        $fx = $this->variantOptionCollection('tag-filter-collection');
        $indirim = Tag::firstOrCreate(['value' => 'INDIRIM']);
        $yeni = Tag::firstOrCreate(['value' => 'YENI']);
        $fx['products']['siyah-gomlek']->tags()->sync([$indirim->id]);
        $fx['products']['beyaz-gomlek']->tags()->sync([$yeni->id]);

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->assertCount('products', 2)
            ->assertCount('tags', 2)
            ->assertSet('tags', fn (array $tags): bool => collect($tags)->contains(
                fn (array $tag): bool => $tag['key'] === 'indirim' && $tag['value'] === 'INDIRIM'
            ))
            ->set('tagFilters', ['indirim' => true])
            ->assertCount('products', 1)
            ->assertSee('Siyah Gömlek')
            ->assertDontSee('Beyaz Gömlek')
            // Çoklu seçim (OR): iki etiket → her iki ürün
            ->set('tagFilters.yeni', true)
            ->assertSet('tagFilters', ['indirim' => true, 'yeni' => true])
            ->assertCount('products', 2)
            ->assertSee('Siyah Gömlek')
            ->assertSee('Beyaz Gömlek')
            // Checkbox kaldırma → yalnız YENI
            ->set('tagFilters.indirim', false)
            ->assertCount('products', 1)
            ->assertSee('Beyaz Gömlek')
            ->assertDontSee('Siyah Gömlek');
    }

    /**
     * clearFilters tüm filtre alanlarını sıfırlamalı ve listeyi geri açmalı;
     * sıralama (sortBy) filtre değildir, korunur.
     */
    public function test_clear_filters_resets_all_filters_and_products(): void
    {
        $fx = $this->variantOptionCollection('clear-filters-collection');
        $brand = Brand::create(['name' => 'Clear '.uniqid()]);
        $fx['products']['siyah-gomlek']->update(['brand_id' => $brand->id]);
        $tag = Tag::firstOrCreate(['value' => 'INDIRIM']);
        $fx['products']['siyah-gomlek']->tags()->sync([$tag->id]);
        $material = $this->attachMaterialAttribute($fx);

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->set('searchTerm', 'Siyah')
            ->set('brandFilters', [Str::slug($brand->name) => true])
            ->set('tagFilters', ['indirim' => true])
            ->set('optionFilters', ['renk' => ['siyah' => true]])
            ->set('attributeFilters', [$material['handle'] => 'Pamuk'])
            ->set('sortBy', 'price_asc')
            ->assertCount('products', 1)
            ->call('clearFilters')
            ->assertSet('searchTerm', '')
            ->assertSet('brandFilters', [])
            ->assertSet('tagFilters', [])
            ->assertSet('optionFilters', [])
            ->assertSet('attributeFilters', [])
            ->assertSet('sortBy', 'price_asc')
            ->assertCount('products', 2);
    }

    /** PLP'de kategori filtresi koleksiyonun kendi id'sini içermemeli (self-filter). */
    public function test_collection_excludes_itself_from_category_filter(): void
    {
        $language = Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => 'self-category-collection',
            'default' => true,
        ]);
        $sibling = Collection::factory()->create();

        $product = Product::factory()->create(['status' => 'published']);
        $product->update([
            'attribute_data' => collect(['name' => new Text('Kardeş Ürün')]),
        ]);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => 'self-cat-product',
            'default' => true,
        ]);
        $collection->products()->attach($product);
        $sibling->products()->attach($product);

        Livewire::test(CollectionPage::class, ['slug' => 'self-category-collection'])
            ->assertCount('products', 1)
            ->assertSet('categories', function (array $categories) use ($collection, $sibling): bool {
                $ids = array_column($categories, 'id');

                return ! in_array($collection->id, $ids, true)
                    && in_array($sibling->id, $ids, true);
            });
    }
    // ────────────────────────────────────────────────────────────
    // P2: Çoklu filtre (AND), URL senkron, sıralama, öznitelik
    // ────────────────────────────────────────────────────────────

    /**
     * P2: Checkbox semantiği — aynı option içinde OR (Renk=Siyah|Beyaz),
     * option'lar arası AND (Renk AND Beden=M).
     */
    public function test_multiple_option_filters_combine_with_and(): void
    {
        $fx = $this->variantOptionCollection('multi-option-collection', withSize: true);

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->assertCount('products', 3)
            ->assertCount('options', 2)
            // OR: Renk = siyah veya beyaz → tüm ürünler
            ->set('optionFilters', ['renk' => ['siyah' => true, 'beyaz' => true]])
            ->assertSet('optionFilters', ['renk' => ['siyah' => true, 'beyaz' => true]])
            ->assertCount('products', 3)
            ->assertSee('Siyah Gömlek')
            ->assertSee('Beyaz Gömlek')
            ->assertSee('Siyah Tişört')
            // AND: beden=m ekle (nested checkbox set) → sadece gömlekler
            ->set('optionFilters.beden.m', true)
            ->assertSet('optionFilters', [
                'renk' => ['siyah' => true, 'beyaz' => true],
                'beden' => ['m' => true],
            ])
            ->assertCount('products', 2)
            ->assertSee('Siyah Gömlek')
            ->assertSee('Beyaz Gömlek')
            ->assertDontSee('Siyah Tişört')
            // OR daralt: beyaz'ı kaldır → siyah AND m
            ->set('optionFilters.renk.beyaz', false)
            ->assertCount('products', 1)
            ->assertSee('Siyah Gömlek')
            ->assertDontSee('Beyaz Gömlek')
            ->assertDontSee('Siyah Tişört');
    }

    /**
     * Ürün başına ayrı yaratılan aynı adlı option'lar ("Renk") tek grupta birleşir:
     * tek "renk" grubu, birleştirilmiş "siyah" değeri her iki ürünün Siyah
     * value'larını kapsar; koleksiyonda olmayan grup ("beden") hiç listelenmez.
     */
    public function test_same_named_options_merge_into_single_filter_group(): void
    {
        $fx = $this->perProductOptionCollection('merged-renk-collection');

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->assertCount('products', 2)
            ->assertSet('options', function (array $options): bool {
                if (count($options) !== 1) {
                    return false;
                }
                $group = $options[0];
                $siyah = collect($group['values'])->firstWhere('key', 'siyah');

                return $group['key'] === 'renk'
                    && $group['name'] === 'Renk'
                    && count($group['option_ids']) === 2
                    && count($group['values']) === 3
                    && $siyah !== null
                    && count($siyah['value_ids']) === 2;
            })
            ->set('optionFilters.renk.siyah', true)
            ->assertCount('products', 2)
            ->assertSee('Siyah Gömlek')
            ->assertSee('Siyah Tişört')
            ->set('optionFilters.renk.siyah', false)
            ->set('optionFilters.renk.kirmizi', true)
            ->assertCount('products', 1)
            ->assertSee('Siyah Tişört')
            ->assertDontSee('Siyah Gömlek');
    }

    /** P2: Sync property'leri #[Url] taşımalı ve query string mount'ta hydrate edilmeli. */
    public function test_filters_and_sort_hydrate_from_query_string(): void
    {
        foreach (['searchTerm', 'selectedCategoryId', 'brandFilters', 'tagFilters', 'optionFilters', 'attributeFilters', 'sortBy'] as $propertyName) {
            $reflection = new ReflectionProperty(CollectionPage::class, $propertyName);
            $this->assertNotEmpty(
                $reflection->getAttributes(Url::class),
                "Property #[Url] senkronuna sahip olmalı: {$propertyName}"
            );
        }

        $fx = $this->variantOptionCollection('query-string-collection');
        $brand = Brand::create(['name' => 'Querystr '.uniqid()]);
        $fx['products']['siyah-gomlek']->update(['brand_id' => $brand->id]);
        $brandKey = Str::slug($brand->name);
        $tag = Tag::firstOrCreate(['value' => 'YENI']);
        $fx['products']['siyah-gomlek']->tags()->sync([$tag->id]);

        Livewire::withQueryParams([
            'q' => 'Siyah',
            'brand' => [$brandKey => true],
            'tags' => ['yeni' => true],
            'options' => ['renk' => ['siyah' => true]],
            'sort' => 'price_asc',
        ])->test(CollectionPage::class, ['slug' => $fx['slug']])
            ->assertSet('searchTerm', 'Siyah')
            ->assertSet('brandFilters', [$brandKey => true])
            ->assertSet('tagFilters', ['yeni' => true])
            ->assertSet('optionFilters', ['renk' => ['siyah' => true]])
            ->assertSet('sortBy', 'price_asc')
            ->assertCount('products', 1)
            ->assertSee('Siyah Gömlek');
    }

    /** P2: Fiyat sıralaması artan/azalan çalışır; bilinmeyen değer 'default'a düşer. */
    public function test_sort_by_price_orders_products(): void
    {
        $fx = $this->variantOptionCollection('price-sort-collection');
        $fx['products']['siyah-gomlek']->variants->first()->prices()->update(['price' => 300]);

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->set('sortBy', 'price_asc')
            ->assertSet('sortBy', 'price_asc')
            ->assertSet('products', fn (array $products): bool => ['Beyaz Gömlek', 'Siyah Gömlek'] === array_column($products, 'name'))
            ->set('sortBy', 'price_desc')
            ->assertSet('products', fn (array $products): bool => ['Siyah Gömlek', 'Beyaz Gömlek'] === array_column($products, 'name'))
            ->set('sortBy', 'bogus-value')
            ->assertSet('sortBy', 'default');
    }

    /** P2: 'newest' sıralaması en yeni ürünü öne alır. */
    public function test_sort_by_newest_orders_products(): void
    {
        $fx = $this->variantOptionCollection('newest-sort-collection');
        // created_at Lunar Product $fillable'ında değildir; forceFill ile yazılır.
        $fx['products']['siyah-gomlek']->forceFill(['created_at' => now()->subDay()])->save();

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->set('sortBy', 'newest')
            ->assertSet('sortBy', 'newest')
            ->assertSet('products', fn (array $products): bool => ['Beyaz Gömlek', 'Siyah Gömlek'] === array_column($products, 'name'));
    }

    /** P2: filterable öznitelik seçenekleri toplanır ve PHP tarafında filtreler. */
    public function test_filterable_attribute_filter_filters_products(): void
    {
        $fx = $this->variantOptionCollection('attribute-filter-collection');
        $material = $this->attachMaterialAttribute($fx);

        Livewire::test(CollectionPage::class, ['slug' => $fx['slug']])
            ->assertCount('products', 2)
            ->assertSet('attributeFilterOptions', function (array $options) use ($material): bool {
                if (count($options) !== 1 || $options[0]['handle'] !== $material['handle']) {
                    return false;
                }
                $values = $options[0]['values'];

                return $options[0]['name'] === 'Materyal'
                    && count($values) === 2
                    && in_array('Pamuk', $values, true)
                    && in_array('Keten', $values, true);
            })
            ->set('attributeFilters', [$material['handle'] => 'Pamuk'])
            ->assertCount('products', 1)
            ->assertSee('Siyah Gömlek')
            ->assertDontSee('Beyaz Gömlek')
            ->set('attributeFilters', [$material['handle'] => 'Keten'])
            ->assertCount('products', 1)
            ->assertSee('Beyaz Gömlek')
            ->assertDontSee('Siyah Gömlek');
    }
}
