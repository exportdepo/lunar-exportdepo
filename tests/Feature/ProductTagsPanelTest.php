<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductResource\Pages;
use Database\Seeders\DemoDataSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ListProducts;
use Illuminate\Support\Facades\DB;
use Lunar\Admin\Models\Staff;
use Lunar\Admin\Support\Forms\Components\Tags as TagsComponent;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Tests\TestCase;

/**
 * Panelde etiket (tag) özelliğinin uçtan uca çalıştığını doğrular:
 * form alanı, kayıt akışı (Tags::syncTags), yeniden yükleme ve TagResource erişimi.
 */
class ProductTagsPanelTest extends TestCase
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

    private function staff(): Staff
    {
        return Staff::factory()->create(['admin' => true]);
    }

    private function defaultCurrency(): Currency
    {
        return Currency::query()->where('default', true)->first()
            ?? Currency::factory()->create(['default' => true]);
    }

    public function test_product_edit_page_contains_the_tags_field(): void
    {
        $product = Product::factory()->create(['status' => 'published']);

        $this->actingAs($this->staff(), 'staff')
            ->get('/exportdepo/products/'.$product->id.'/edit')
            ->assertOk()
            ->assertSee('Etiketler');
    }

    public function test_tags_can_be_saved_to_a_product_and_reload_from_relationship(): void
    {
        $staff = $this->staff();
        $product = Product::factory()->create(['status' => 'published']);

        $this->actingAs($staff, 'staff');

        TagsComponent::make('tags')->syncTags($product, ['Yeni Sezon', 'Cok Satan']);

        $this->assertDatabaseHas('lunar_tags', ['value' => 'YENI SEZON']);
        $this->assertDatabaseHas('lunar_tags', ['value' => 'COK SATAN']);

        $taggableType = $product->getMorphClass();
        $tagIds = DB::table('lunar_tags')->whereIn('value', ['YENI SEZON', 'COK SATAN'])->pluck('id');

        foreach ($tagIds as $tagId) {
            $this->assertDatabaseHas('lunar_taggables', [
                'tag_id' => $tagId,
                'taggable_id' => $product->id,
                'taggable_type' => $taggableType,
            ]);
        }

        $this->assertEqualsCanonicalizing(
            ['YENI SEZON', 'COK SATAN'],
            $product->fresh()->tags()->pluck('value')->all()
        );

        $this->actingAs($staff, 'staff')
            ->get('/exportdepo/products/'.$product->id.'/edit')
            ->assertOk()
            ->assertSee('YENI SEZON')
            ->assertSee('COK SATAN');
    }

    public function test_tags_can_be_removed_from_a_product(): void
    {
        $product = Product::factory()->create(['status' => 'published']);

        TagsComponent::make('tags')->syncTags($product, ['Yeni Sezon', 'Cok Satan']);
        $this->assertSame(2, $product->fresh()->tags()->count());

        TagsComponent::make('tags')->syncTags($product, []);

        $this->assertSame(0, $product->fresh()->tags()->count());
        $this->assertDatabaseMissing('lunar_taggables', [
            'taggable_id' => $product->id,
            'taggable_type' => $product->getMorphClass(),
        ]);
    }

    public function test_tag_resource_index_is_reachable_in_the_panel(): void
    {
        $this->actingAs($this->staff(), 'staff')
            ->get('/exportdepo/tags')
            ->assertOk()
            ->assertSee('Etiket');
    }

    public function test_application_product_resource_keeps_lunar_pages_and_adds_tags_column(): void
    {
        $this->assertSame(
            \Lunar\Admin\Filament\Resources\ProductResource::class,
            get_parent_class(\App\Filament\Resources\ProductResource::class)
        );

        $this->assertContains(
            \App\Filament\Resources\ProductResource::class,
            \App\Providers\CleanLunarPanelManager::getResources()
        );

        $columns = \App\Filament\Resources\ProductResource::getTableColumns();

        $this->assertContains(
            'tags.value',
            array_map(fn ($column) => $column->getName(), $columns)
        );

        $this->defaultCurrency();

        $this->actingAs($this->staff(), 'staff')
            ->get('/exportdepo/products')
            ->assertOk();
    }

    public function test_products_list_shows_tag_badges_and_tag_filter(): void
    {
        $tagged = Product::factory()->create(['status' => 'published']);
        TagsComponent::make('tags')->syncTags($tagged, ['Yeni Sezon']);
        $untagged = Product::factory()->create(['status' => 'published']);

        $this->defaultCurrency();

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertOk()
            ->assertCanSeeTableRecords([$tagged]);

        $tagId = [$tagged->tags()->first()->id];

        $query = \App\Filament\Resources\ProductResource::getEloquentQuery();
        $query->whereHas('tags', fn ($q) => $q->whereIn('lunar_tags.id', $tagId));

        $this->assertTrue($query->get()->contains($tagged));
        $this->assertFalse($query->get()->contains($untagged));

        $columns = \App\Filament\Resources\ProductResource::getTableColumns();
        $tagsColumn = collect($columns)->firstWhere(fn ($column) => $column->getName() === 'tags.value');

        $this->assertNotNull($tagsColumn);
        $this->assertSame('Etiketler', $tagsColumn->getLabel());
        $this->assertTrue($tagsColumn->isBadge());

        $this->assertContains(
            \App\Filament\Resources\ProductResource::class,
            \App\Providers\CleanLunarPanelManager::getResources()
        );
    }

    public function test_demo_seeder_attaches_tags_to_products_idempotently(): void
    {
        $seeder = new DemoDataSeeder;
        $ref = new \ReflectionMethod($seeder, 'seedTags');
        $product = Product::factory()->create(['status' => 'published']);

        $ref->invoke($seeder, $product, ['tags' => ['Yeni Sezon', 'Cok Satan']]);
        $ref->invoke($seeder, $product, ['tags' => ['Yeni Sezon', 'Cok Satan']]);

        $this->assertEqualsCanonicalizing(
            ['YENI SEZON', 'COK SATAN'],
            $product->fresh()->tags()->pluck('value')->all()
        );
        $this->assertSame(2, DB::table('lunar_tags')->whereIn('value', ['YENI SEZON', 'COK SATAN'])->count());
    }
}
