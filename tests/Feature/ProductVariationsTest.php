<?php

namespace Tests\Feature;

use App\Livewire\Storefront\ProductPage;
use Livewire\Livewire;
use Lunar\Admin\Models\Staff;
use Lunar\Facades\CartSession;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;
use Tests\TestCase;

class ProductVariationsTest extends TestCase
{
    protected function tearDown(): void
    {
        CartSession::forget();

        parent::tearDown();
    }

    public function test_product_with_options_renders_colour_swatches_and_size_buttons(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();

        Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
            ->assertSee('Renk')
            ->assertSee('Beden')
            ->assertSeeHtml('wire:click="selectOptionValue(')
            // Renk seçeneği hex koduyla swatch olarak çizilir.
            ->assertSeeHtml('#111827')
            // Seçenekli üründe varyant dropdown'ı yerine seçenek butonları görünür.
            ->assertDontSeeHtml('id="variant"')
            // Varsayılan seçim stokta olan ilk kombinasyondur.
            ->assertSet('selectedVariantId', $fixture['variants']['siyah-s']->id)
            ->assertSet('product.variant_id', $fixture['variants']['siyah-s']->id)
            ->assertSet('product.stock', 5);
    }

    public function test_selecting_size_updates_price_stock_and_cart_variant(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();
        $target = $fixture['variants']['siyah-m'];

        $component = Livewire::test(ProductPage::class, ['slug' => $fixture['slug']]);

        $component->call('selectOptionValue', $fixture['options']['beden']->id, $fixture['values']['beden']['M']->id)
            ->assertSet('selectedVariantId', $target->id)
            ->assertSet('product.variant_id', $target->id)
            ->assertSet('product.stock', 5)
            ->assertSet('product.price', $target->prices->first()?->price?->value);

        $component->call('addToCart');

        $line = CartSession::current()->lines->first();
        $this->assertSame($target->id, $line->purchasable_id);
        $this->assertSame((new $target)->getMorphClass(), $line->purchasable_type);
        $this->assertSame(1, $line->quantity);
    }

    public function test_out_of_stock_and_missing_combinations_are_reported_unavailable(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();

        $page = Livewire::test(ProductPage::class, ['slug' => $fixture['slug']]);

        // Siyah seçiliyken: S ve M stokta, 2XL stok dışı.
        $this->assertTrue($page->instance()->isValueAvailable($fixture['options']['beden']->id, $fixture['values']['beden']['M']->id));
        $this->assertFalse($page->instance()->isValueAvailable($fixture['options']['beden']->id, $fixture['values']['beden']['2XL']->id));

        // Tüm Beyaz varyantlar stokta olduğu için renk müsait.
        $this->assertTrue($page->instance()->isValueAvailable($fixture['options']['renk']->id, $fixture['values']['renk']['Beyaz']->id));

        // Müsait olmayan kombinasyon vitrinde soluk/çizili ve tıklanamaz gösterilir.
        $page->assertSeeHtml('line-through')
            ->assertSeeHtml('disabled');
    }

    public function test_missing_combination_snaps_selection_to_first_available_variant(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();

        $page = Livewire::test(ProductPage::class, ['slug' => $fixture['slug']]);

        // 2XL seçildiğinde yalnızca Siyah/2XL var ve stok dışı.
        $page->call('selectOptionValue', $fixture['options']['beden']->id, $fixture['values']['beden']['2XL']->id)
            ->assertSet('selectedVariantId', $fixture['variants']['siyah-2xl']->id)
            ->assertSet('product.stock', 0);

        // Beyaz/2XL kombinasyonu hiç üretilmemiş: seçim ilk müsait varyanta kayar.
        $page->call('selectOptionValue', $fixture['options']['renk']->id, $fixture['values']['renk']['Beyaz']->id)
            ->assertSet('selectedVariantId', $fixture['variants']['beyaz-s']->id)
            ->assertSet('product.variant_id', $fixture['variants']['beyaz-s']->id)
            ->assertSet('product.stock', 5);

        $this->assertTrue($page->instance()->isValueSelected($fixture['options']['beden']->id, $fixture['values']['beden']['S']->id));
    }

    public function test_products_without_options_keep_the_variant_dropdown(): void
    {
        $fixture = $this->productWithoutOptionsWithTwoVariants();

        Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
            ->assertSet('optionGroups', [])
            ->assertSeeHtml('id="variant"')
            ->assertDontSeeHtml('selectOptionValue(')
            ->assertSet('selectedVariantId', $fixture['variants'][0]->id);
    }

    public function test_seeded_option_values_are_managed_from_the_panel(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();
        $staff = Staff::factory()->create(['admin' => true]);

        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/product-options')
            ->assertOk()
            ->assertSee('Renk')
            ->assertSee('Beden');

        $variant = $fixture['variants']['siyah-s'];

        // Lunar, eşlenmiş attribute'u olmayan varyantın düzenleme sayfasını
        // tanımlayıcı (SKU/GTIN/MPN/EAN) sayfasına yönlendirir.
        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/product-variants/'.$variant->id.'/edit')
            ->assertRedirect('/exportdepo/product-variants/'.$variant->id.'/identifiers');

        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/product-variants/'.$variant->id.'/identifiers')
            ->assertOk()
            ->assertSee($variant->sku);

        // Envanter sayfası stok/stok dışı değerini panelde gösterir.
        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/product-variants/'.$variant->id.'/inventory')
            ->assertOk();
    }

    public function test_panel_product_variants_page_lists_generated_combinations(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();
        $staff = Staff::factory()->create(['admin' => true]);

        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/products/'.$fixture['product']->id.'/variants')
            ->assertOk()
            ->assertSee($fixture['variants']['siyah-s']->sku)
            ->assertSee($fixture['variants']['beyaz-m']->sku);
    }

    /**
     * Vitrin testleri için asgari kanal / para birimi / müşteri grubu bağlamı.
     *
     * @return array{taxClass: TaxClass, currency: Currency, language: Language, channel: Channel, customerGroup: CustomerGroup}
     */
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

        Channel::query()->where('default', true)->update(['default' => false]);
        $channel = Channel::query()->firstOrCreate(
            ['handle' => 'web'],
            ['name' => 'Web', 'url' => config('app.url'), 'default' => true],
        );
        $channel->update(['default' => true]);

        CustomerGroup::query()->where('default', true)->update(['default' => false]);
        $customerGroup = CustomerGroup::query()->firstOrCreate(
            ['handle' => 'default'],
            ['name' => 'Standard', 'default' => true],
        );
        $customerGroup->update(['default' => true]);

        return [
            'taxClass' => TaxClass::factory()->create(),
            'currency' => $currency,
            'language' => $language,
            'channel' => $channel,
            'customerGroup' => $customerGroup,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function publishProduct(array $context, string $slug): Product
    {
        $product = Product::factory()->create(['status' => 'published']);

        $product->channels()->syncWithoutDetaching([
            $context['channel']->id => ['enabled' => true],
        ]);
        $product->customerGroups()->syncWithoutDetaching([
            $context['customerGroup']->id => ['enabled' => true, 'purchasable' => true],
        ]);
        $product->urls()->create([
            'language_id' => $context['language']->id,
            'slug' => $slug,
            'default' => true,
        ]);

        return $product;
    }

    /**
     * Renk (swatch) + Beden seçenekli; biri stok dışı, biri hiç üretilmemiş
     * kombinasyona sahip ürün üretir.
     *
     * Kombinasyonlar: Siyah/S, Siyah/M, Siyah/2XL (stok 0), Beyaz/S, Beyaz/M
     * (Beyaz/2XL hiç yok).
     *
     * @return array<string, mixed>
     */
    private function productWithColourAndSizeVariants(): array
    {
        $context = $this->storefrontContext();
        $slug = 'test-variations-'.uniqid();

        $product = $this->publishProduct($context, $slug);

        [$renk, $renkValues] = $this->createOption($product, 'renk', 'Renk', [
            ['name' => 'Siyah', 'hex' => '#111827'],
            ['name' => 'Beyaz', 'hex' => '#f1f5f9'],
        ]);
        [$beden, $bedenValues] = $this->createOption($product, 'beden', 'Beden', [
            ['name' => 'S'],
            ['name' => 'M'],
            ['name' => '2XL'],
        ]);

        $product->productOptions()->sync([
            $renk->id => ['position' => 1],
            $beden->id => ['position' => 2],
        ]);

        $definitions = [
            ['renk' => 'Siyah', 'beden' => 'S', 'stock' => 5, 'price' => 34900],
            ['renk' => 'Siyah', 'beden' => 'M', 'stock' => 5, 'price' => 35900],
            ['renk' => 'Siyah', 'beden' => '2XL', 'stock' => 0, 'price' => 37900],
            ['renk' => 'Beyaz', 'beden' => 'S', 'stock' => 5, 'price' => 34900],
            ['renk' => 'Beyaz', 'beden' => 'M', 'stock' => 5, 'price' => 35900],
        ];

        $variants = [];

        foreach ($definitions as $index => $definition) {
            $variant = $product->variants()->create([
                'tax_class_id' => $context['taxClass']->id,
                'sku' => 'TEST-VAR-'.$index.'-'.uniqid(),
                'stock' => $definition['stock'],
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'shippable' => true,
                'purchasable' => 'always',
            ]);

            $variant->values()->sync([
                $renkValues[$definition['renk']]->id,
                $bedenValues[$definition['beden']]->id,
            ]);

            $variant->prices()->create([
                'currency_id' => $context['currency']->id,
                'price' => $definition['price'],
                'min_quantity' => 1,
            ]);

            $key = strtolower($definition['renk']).'-'.strtolower($definition['beden']);

            $variants[$key] = $variant->fresh(['prices.currency']);
        }

        return [
            'product' => $product->fresh(['variants.prices.currency', 'variants.values', 'urls']),
            'slug' => $slug,
            'variants' => $variants,
            'options' => ['renk' => $renk, 'beden' => $beden],
            'values' => ['renk' => $renkValues, 'beden' => $bedenValues],
        ];
    }

    /**
     * Seçeneği olmayan, iki varyantlı ürün (geriye dönük dropdown davranışı).
     *
     * @return array<string, mixed>
     */
    private function productWithoutOptionsWithTwoVariants(): array
    {
        $context = $this->storefrontContext();
        $slug = 'test-no-options-'.uniqid();

        $product = $this->publishProduct($context, $slug);
        $variants = [];

        foreach ([100, 200] as $index => $price) {
            $variant = $product->variants()->create([
                'tax_class_id' => $context['taxClass']->id,
                'sku' => 'TEST-NO-OPTION-'.$index.'-'.uniqid(),
                'stock' => 5 + $index,
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'shippable' => true,
                'purchasable' => 'always',
            ]);

            $variant->prices()->create([
                'currency_id' => $context['currency']->id,
                'price' => $price,
                'min_quantity' => 1,
            ]);

            $variants[] = $variant->fresh(['prices.currency']);
        }

        return [
            'product' => $product->fresh(['variants.prices.currency', 'variants.values', 'urls']),
            'slug' => $slug,
            'variants' => $variants,
        ];
    }

    /**
     * Ürüne seçenek ve sıralı değerlerini ekler.
     *
     * @param  array<int, array{name: string, hex?: string}>  $values
     * @return array{0: ProductOption, 1: array<string, ProductOptionValue>}
     */
    private function createOption(Product $product, string $handle, string $name, array $values): array
    {
        $option = ProductOption::create([
            'handle' => $handle,
            'name' => ['tr' => $name, 'en' => $name],
            'shared' => false,
        ]);

        $created = [];

        foreach ($values as $index => $value) {
            $created[$value['name']] = $option->values()->create([
                'name' => ['tr' => $value['name'], 'en' => $value['name']],
                'position' => $index + 1,
                'meta' => isset($value['hex']) ? ['hex' => $value['hex']] : null,
            ]);
        }

        return [$option->fresh('values'), $created];
    }
}
