<?php

namespace Tests\Feature;

use Lunar\Models\Collection;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class StorefrontRoutesTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();

        $language = Language::where('default', true)->first();

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => 'elektronik',
            'default' => true,
        ]);

        $product = Product::factory()->create(['status' => 'published']);
        $collection->products()->attach($product);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => 'lunarbook-pro-14',
            'default' => true,
        ]);
    }

    public function test_storefront_pages_render_successfully(): void
    {
        foreach ([
            '/',
            '/koleksiyon/elektronik',
            '/urun/lunarbook-pro-14',
            '/sepet',
            '/checkout',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_unknown_storefront_slugs_return_not_found(): void
    {
        $this->get('/koleksiyon/bulunamayan')->assertNotFound();
        $this->get('/urun/bulunamayan')->assertNotFound();
    }

    public function test_homepage_contains_cart_indicator(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('cart-dropdown')
            ->assertSee('cart-badge');
    }
}
