<?php

namespace Tests\Feature;

use Illuminate\Support\Str;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Sitemap XML çıktısını doğrular: ana sayfa, koleksiyonlar ve yayındaki
 * ürünler listelenir; taslak ürünler hariç tutulur.
 */
class SitemapTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        Currency::factory()->create(['code' => 'TRY', 'default' => true]);
        Language::factory()->create(['code' => 'tr', 'default' => true]);
    }

    public function test_sitemap_renders_xml_with_home_collections_and_products(): void
    {
        $language = Language::query()->where('default', true)->first();

        $product = Product::factory()->create(['status' => 'published']);
        $product->urls()->create(['language_id' => $language->id, 'slug' => 'sitemap-urun-'.Str::ulid(), 'default' => true]);

        $collection = Collection::factory()->create();
        $collection->urls()->create(['language_id' => $language->id, 'slug' => 'sitemap-koleksiyon-'.Str::ulid(), 'default' => true]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<urlset', false)
            ->assertSee(url('/'), false)
            ->assertSee(url('/urun/'.$product->urls->first()->slug), false)
            ->assertSee(url('/koleksiyon/'.$collection->urls->first()->slug), false);
    }

    public function test_sitemap_excludes_draft_products(): void
    {
        $language = Language::query()->where('default', true)->first();

        $draft = Product::factory()->create(['status' => 'draft']);
        $draft->urls()->create(['language_id' => $language->id, 'slug' => 'taslak-'.Str::ulid(), 'default' => true]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('taslak-', false);
    }
}