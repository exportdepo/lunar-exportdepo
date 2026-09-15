<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CollectionPage;
use Livewire\Livewire;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Models\Language;
use Lunar\FieldTypes\Text;
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
}
