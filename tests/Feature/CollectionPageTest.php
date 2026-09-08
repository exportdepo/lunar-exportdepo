<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CollectionPage;
use Livewire\Livewire;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Models\Language;
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
        $product1->translateAttribute('name', 'Apple iPhone', 'en');
        $collection->products()->attach($product1);
        $product1->urls()->create([
            'language_id' => $language->id,
            'slug' => 'iphone',
            'default' => true,
        ]);

        $product2 = Product::factory()->create(['status' => 'published']);
        $product2->translateAttribute('name', 'Samsung Galaxy', 'en');
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
}
