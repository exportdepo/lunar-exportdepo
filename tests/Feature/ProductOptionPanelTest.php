<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductOptionResource;
use App\Filament\Resources\ProductOptionResource\Pages\EditProductOption;
use App\Filament\Resources\ProductOptionResource\RelationManagers\ValuesRelationManager;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Lunar\Admin\Models\Staff;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\TaxClass;
use Tests\TestCase;

/**
 * Panelde renk/beden gibi ürün seçeneklerinin (varyasyonların) yönetimi.
 */
class ProductOptionPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Language::factory()->create([
            'code' => 'tr',
            'default' => true,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('lunar'));
    }

    public function test_product_option_resource_is_replaced_by_the_application_resource(): void
    {
        $resources = ProductOptionResource::getRelations();

        $this->assertSame([ValuesRelationManager::class], $resources);
    }

    public function test_colour_option_values_expose_a_swatch_and_a_colour_picker(): void
    {
        $option = $this->colourOption();

        $page = Livewire::test(ValuesRelationManager::class, [
            'ownerRecord' => $option,
            'pageClass' => EditProductOption::class,
        ]);

        $page->assertCanSeeTableRecords($option->values)
            ->assertSee('Renk kodu');
    }

    public function test_admin_can_open_the_product_options_page_in_the_panel(): void
    {
        $staff = Staff::factory()->create(['admin' => true]);
        $option = $this->colourOption();

        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/product-options')
            ->assertOk();

        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/product-options/'.$option->id.'/edit')
            ->assertOk();
    }

    public function test_admin_can_manage_variants_of_a_variation_product_in_the_panel(): void
    {
        $staff = Staff::factory()->create(['admin' => true]);
        $product = $this->variationProduct();

        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/products/'.$product->id.'/edit')
            ->assertOk();
    }

    /**
     * Panelde görünürlüğü doğrulamak için renk + beden seçenekli ürün üretir.
     */
    private function variationProduct(): Product
    {
        $product = Product::factory()->create(['status' => 'published']);

        $colour = ProductOption::create([
            'handle' => 'renk-'.uniqid(),
            'name' => ['tr' => 'Renk', 'en' => 'Colour'],
            'shared' => false,
        ]);
        $size = ProductOption::create([
            'handle' => 'beden-'.uniqid(),
            'name' => ['tr' => 'Beden', 'en' => 'Size'],
            'shared' => false,
        ]);

        $colourValues = collect(['Siyah' => '#111827', 'Beyaz' => '#f1f5f9'])
            ->map(fn (string $hex, string $name) => $colour->values()->create([
                'name' => ['tr' => $name, 'en' => $name],
                'meta' => ['hex' => $hex],
            ]));
        $sizeValues = collect(['S', 'M'])
            ->map(fn (string $name) => $size->values()->create([
                'name' => ['tr' => $name, 'en' => $name],
            ]));

        $product->productOptions()->sync([
            $colour->id => ['position' => 1],
            $size->id => ['position' => 2],
        ]);

        foreach ($colourValues as $colourValue) {
            foreach ($sizeValues as $sizeValue) {
                $variant = $product->variants()->create([
                    'tax_class_id' => TaxClass::factory()->create()->id,
                    'sku' => 'PANEL-VAR-'.uniqid(),
                    'stock' => 3,
                    'unit_quantity' => 1,
                    'min_quantity' => 1,
                    'quantity_increment' => 1,
                    'shippable' => true,
                    'purchasable' => 'always',
                ]);

                $variant->values()->sync([$colourValue->id, $sizeValue->id]);
            }
        }

        return $product->fresh(['variants', 'productOptions']);
    }

    private function colourOption(): ProductOption
    {
        $option = ProductOption::create([
            'handle' => 'renk-'.uniqid(),
            'name' => ['tr' => 'Renk', 'en' => 'Colour'],
            'shared' => false,
        ]);

        foreach ([['Siyah', '#111827'], ['Beyaz', '#f1f5f9']] as $position => [$name, $hex]) {
            $option->values()->create([
                'name' => ['tr' => $name, 'en' => $name],
                'position' => $position + 1,
                'meta' => ['hex' => $hex],
            ]);
        }

        return $option->fresh('values');
    }
}
