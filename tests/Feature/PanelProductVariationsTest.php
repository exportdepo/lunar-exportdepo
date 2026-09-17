<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductOptionResource\Pages\EditProductOption;
use App\Filament\Resources\ProductOptionResource\RelationManagers\ValuesRelationManager;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Lunar\Admin\Models\Staff;
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

/**
 * Panelde (Lunar admin) varyasyon yönetiminin demo verisiyle çalıştığını
 * doğrular: seçenek CRUD'u, ürünün varyant üretici ekranı ve varyant listesi.
 */
class PanelProductVariationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'code' => 'tr-'.uniqid(),
            'default' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('lunar'));
    }

    public function test_product_options_list_shows_seeded_options_and_values(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();

        $this->actingAs($this->staff(), 'staff')
            ->get('/exportdepo/product-options')
            ->assertOk()
            ->assertSee('Renk')
            ->assertSee('Beden');
    }

    public function test_product_option_edit_page_relation_manager_lists_values(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();
        $option = $fixture['options']['renk'];

        // Değer tablosu bir Livewire bileşeni olarak yüklendiği için doğrudan
        // relation manager üzerinden test edilir.
        Livewire::test(ValuesRelationManager::class, [
            'ownerRecord' => $option,
            'pageClass' => EditProductOption::class,
        ])
            ->assertOk()
            ->assertCanSeeTableRecords($option->values)
            ->assertSee('Siyah')
            ->assertSee('Beyaz')
            ->assertSee('Renk kodu');

        $this->actingAs($this->staff(), 'staff')
            ->get('/exportdepo/product-options/'.$option->id.'/edit')
            ->assertOk();
    }

    public function test_manage_product_variants_page_lists_generated_combinations(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();

        $response = $this->actingAs($this->staff(), 'staff')
            ->get('/exportdepo/products/'.$fixture['product']->id.'/variants')
            ->assertOk();

        // Üretilen tüm kombinasyon SKU'ları panelde listelenir.
        foreach ($fixture['variants'] as $variant) {
            $response->assertSee($variant->sku);
        }
    }

    public function test_combination_variant_can_be_edited_in_the_panel(): void
    {
        $fixture = $this->productWithColourAndSizeVariants();
        $variant = $fixture['variants'][1];

        // Kombinasyon varyantı panelde SKU'su ile açılır (öznitelikler sayfası).
        $this->actingAs($this->staff(), 'staff')
            ->get('/exportdepo/product-variants/'.$variant->id.'/identifiers')
            ->assertOk()
            ->assertSee($variant->sku);

        // Varyant, üretildiği iki seçenek değerine (renk + beden) bağlı kalır.
        $this->assertSame(2, $variant->values()->count());
        $this->assertSame(
            ['M', 'Siyah'],
            $variant->values()
                ->get()
                ->map(fn (ProductOptionValue $value) => $value->translate('name'))
                ->sort()
                ->values()
                ->all()
        );
    }

    private function staff(): Staff
    {
        return Staff::factory()->create(['admin' => true]);
    }

    /**
     * Renk (hex meta'lı) + Beden seçenekli ürün ve kombinasyon varyantları.
     *
     * @return array<string, mixed>
     */
    private function productWithColourAndSizeVariants(): array
    {
        TaxZone::query()->where('default', true)->exists() || TaxZone::factory()->create();

        $language = Language::factory()->create(['code' => 'tr-'.uniqid(), 'default' => true]);
        Currency::query()->where('default', true)->update(['default' => false]);
        $currency = Currency::factory()->create(['code' => 'TRY'.uniqid(), 'default' => true]);

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

        $taxClass = TaxClass::factory()->create();

        $product = Product::factory()->create(['status' => 'published']);
        $product->channels()->syncWithoutDetaching([$channel->id => ['enabled' => true]]);
        $product->customerGroups()->syncWithoutDetaching([
            $customerGroup->id => ['enabled' => true, 'purchasable' => true],
        ]);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => 'panel-variations-'.uniqid(),
            'default' => true,
        ]);

        $renk = ProductOption::create(['handle' => 'renk', 'name' => ['tr' => 'Renk'], 'shared' => false]);
        $renkValues = [];

        foreach ([['Siyah', '#111827'], ['Beyaz', '#f1f5f9']] as $index => [$name, $hex]) {
            $renkValues[$name] = $renk->values()->create([
                'name' => ['tr' => $name],
                'position' => $index + 1,
                'meta' => ['hex' => $hex],
            ]);
        }

        $beden = ProductOption::create(['handle' => 'beden', 'name' => ['tr' => 'Beden'], 'shared' => false]);
        $bedenValues = [];

        foreach (['S', 'M'] as $index => $name) {
            $bedenValues[$name] = $beden->values()->create([
                'name' => ['tr' => $name],
                'position' => $index + 1,
            ]);
        }

        $product->productOptions()->sync([
            $renk->id => ['position' => 1],
            $beden->id => ['position' => 2],
        ]);

        $skuPrefix = 'PANEL-VAR-'.uniqid();
        $variants = [];

        foreach ([['Siyah', 'S'], ['Siyah', 'M'], ['Beyaz', 'S']] as $index => [$renkName, $bedenName]) {
            $variant = $product->variants()->create([
                'tax_class_id' => $taxClass->id,
                'sku' => $skuPrefix.'-'.$index.'-'.$renkName.'-'.$bedenName,
                'stock' => 5,
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'shippable' => true,
                'purchasable' => 'always',
            ]);

            $variant->values()->sync([
                $renkValues[$renkName]->id,
                $bedenValues[$bedenName]->id,
            ]);

            $variant->prices()->create([
                'currency_id' => $currency->id,
                'price' => 34900,
                'min_quantity' => 1,
            ]);

            $variants[] = $variant->fresh(['values']);
        }

        return [
            'product' => $product->fresh(['variants.values', 'urls']),
            'skuPrefix' => $skuPrefix,
            'variants' => $variants,
            'options' => ['renk' => $renk->fresh('values'), 'beden' => $beden->fresh('values')],
            'values' => ['renk' => $renkValues, 'beden' => $bedenValues],
        ];
    }
}
