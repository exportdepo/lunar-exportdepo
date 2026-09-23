<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CollectionPage;
use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\ProductPage;
use App\Livewire\Storefront\WishlistPage;
use App\Models\ProductReview;
use App\Support\WishlistManager;
use Livewire\Livewire;
use Lunar\Models\Collection;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\Tag;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

class StorefrontTagsTest extends TestCase
{
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    public function test_home_page_product_card_shows_tag_badges(): void
    {
        $this->productWithTags('tagged-home-product', ['YENİ', 'ÇOK SATAN']);

        $response = $this->get('/')
            ->assertOk()
            ->assertSee('data-testid="card-tags"', false)
            ->assertSee('YENİ')
            ->assertSee('ÇOK SATAN');

        // Rozet, ürün bağlantısının (başlığın) ardından gelir — başlık altı konumu güvence altında.
        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'data-testid="card-tags"'),
            strpos($html, '/urun/tagged-home-product'),
            'Etiket rozeti ürün başlığından sonra gelmeli.'
        );
    }

    public function test_home_page_hides_tag_badges_for_products_without_tags(): void
    {
        $this->productWithTags('untagged-home-product', []);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-testid="card-tags"', false);
    }

    public function test_product_page_shows_all_tag_badges_under_title(): void
    {
        $this->productWithTags('tagged-pdp-product', ['YENİ', 'İNDİRİM']);

        $this->get('/urun/tagged-pdp-product')
            ->assertOk()
            ->assertSee('data-testid="product-tags"', false)
            ->assertSee('YENİ')
            ->assertSee('İNDİRİM');
    }

    public function test_product_page_hides_tag_section_for_products_without_tags(): void
    {
        $this->productWithTags('untagged-pdp-product', []);

        $this->get('/urun/untagged-pdp-product')
            ->assertOk()
            ->assertDontSee('data-testid="product-tags"', false);
    }

    public function test_collection_page_product_cards_show_tag_badges(): void
    {
        $language = Language::where('default', true)->firstOrFail();

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => 'tagged-collection',
            'default' => true,
        ]);

        $product = $this->productWithTags('tagged-collection-product', ['YENİ']);
        $collection->products()->attach($product);

        Livewire::test(CollectionPage::class, ['slug' => 'tagged-collection'])
            ->assertCount('products', 1)
            ->assertSee('data-testid="card-tags"', false)
            ->assertSee('YENİ');
    }

    public function test_wishlist_page_shows_tag_badges(): void
    {
        $product = $this->productWithTags('tagged-wishlist-product', ['YENİ']);

        session([WishlistManager::SESSION_KEY => [$product->id]]);

        $component = Livewire::test(WishlistPage::class)
            ->assertCount('items', 1)
            ->assertSee('data-testid="wishlist-tags"', false)
            ->assertSee('YENİ');

        // Rozet, ürün adının (h3) ardından gelir — başlık altı konumu güvence altında.
        $html = $component->html();
        $this->assertLessThan(
            strpos($html, 'data-testid="wishlist-tags"'),
            strpos($html, '/urun/tagged-wishlist-product'),
            'Etiket rozeti ürün adından sonra gelmeli.'
        );
    }

    public function test_home_page_card_shows_rating_summary_above_tag_badges(): void
    {
        $product = $this->productWithTags('rated-home-product', ['YENİ']);
        $this->approveReview($product->id, 5);
        $this->approveReview($product->id, 4);

        $html = $this->get('/')
            ->assertOk()
            ->assertSee('data-testid="card-rating"', false)
            ->assertSee('>4.5</span>', false)
            ->assertSee('(2)', false)
            ->assertSee('data-testid="card-tags"', false)
            ->getContent();

        $ratingPos = strpos($html, 'data-testid="card-rating"');
        $tagsPos = strpos($html, 'data-testid="card-tags"');

        $this->assertNotFalse($ratingPos, 'Değerlendirme satırı görünmeli.');
        $this->assertGreaterThan($ratingPos, $tagsPos, 'Değerlendirme satırı etiket rozetlerinin üstünde olmalı.');
    }

    public function test_home_page_card_hides_rating_without_approved_reviews(): void
    {
        $product = $this->productWithTags('unrated-home-product', []);
        $this->approveReview($product->id, 5, approved: false);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-testid="card-rating"', false);
    }

    public function test_collection_page_card_shows_rating_summary(): void
    {
        $language = Language::where('default', true)->firstOrFail();

        $collection = Collection::factory()->create();
        $collection->urls()->create([
            'language_id' => $language->id,
            'slug' => 'rated-collection',
            'default' => true,
        ]);

        $product = $this->productWithTags('rated-collection-product', []);
        $collection->products()->attach($product);
        $this->approveReview($product->id, 4);

        Livewire::test(CollectionPage::class, ['slug' => 'rated-collection'])
            ->assertCount('products', 1)
            ->assertSee('data-testid="card-rating"', false)
            ->assertSee('>4.0</span>', false)
            ->assertSee('(1)', false);
    }

    public function test_wishlist_page_shows_rating_summary_above_tag_badges(): void
    {
        $product = $this->productWithTags('rated-wishlist-product', ['YENİ']);
        $this->approveReview($product->id, 3);
        $this->approveReview($product->id, 5);

        session([WishlistManager::SESSION_KEY => [$product->id]]);

        $html = Livewire::test(WishlistPage::class)
            ->assertCount('items', 1)
            ->assertSee('data-testid="wishlist-rating"', false)
            ->assertSee('>4.0</span>', false)
            ->assertSee('(2)', false)
            ->html();

        $ratingPos = strpos($html, 'data-testid="wishlist-rating"');
        $tagsPos = strpos($html, 'data-testid="wishlist-tags"');

        $this->assertNotFalse($ratingPos, 'Değerlendirme satırı görünmeli.');
        $this->assertGreaterThan($ratingPos, $tagsPos, 'Değerlendirme satırı etiket rozetlerinin üstünde olmalı.');
    }

    /**
     * Onaylı (ya da onaysız) ürün değerlendirmesi oluşturur.
     */
    private function approveReview(int $productId, int $rating, bool $approved = true): void
    {
        ProductReview::create([
            'product_id' => $productId,
            'user_id' => null,
            'name' => 'Test Kullanıcısı',
            'rating' => $rating,
            'title' => null,
            'body' => 'Test değerlendirmesi.',
            'is_approved' => $approved,
        ]);
    }

    /**
     * Yayınlanmış, URL'si olan ve isteğe bağlı etiketlere sahip ürün oluşturur.
     *
     * @param  array<int, string>  $tagValues
     */
    private function productWithTags(string $slug, array $tagValues): Product
    {
        $language = Language::where('default', true)->firstOrFail();

        $product = Product::factory()->create(['status' => 'published']);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => $slug,
            'default' => true,
        ]);

        if ($tagValues !== []) {
            $tagIds = array_map(
                fn (string $value): int => Tag::firstOrCreate(['value' => $value])->id,
                $tagValues
            );

            $product->tags()->syncWithoutDetaching($tagIds);
        }

        return $product;
    }
}