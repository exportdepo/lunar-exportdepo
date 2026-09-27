<?php

namespace Tests\Feature;

use App\Livewire\Storefront\ProductPage;
use App\Models\ProductReview;
use Livewire\Livewire;
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
            ->assertSee('cart-drawer')
            ->assertSee('cart-badge');
    }

    public function test_homepage_footer_renders_rich_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Hızlı Teslimat')
            ->assertSee('Güvenli Ödeme')
            ->assertSee('14 Gün İade')
            ->assertSee('7/24 Destek')
            ->assertSee('Fırsatlardan ilk siz haberdar olun')
            ->assertSee('Sepetim')
            ->assertSee('Sipariş Takibi')
            ->assertSee('newsletter-email')
            ->assertSee('Kurumsal')
            ->assertSee('Hakkımızda')
            ->assertSee('Gizlilik ve KVKK')
            ->assertSee('İade ve Değişim')
            ->assertSee('Sıkça Sorulan Sorular');
    }

    public function test_product_page_shows_enriched_sections(): void
    {
        $this->get('/urun/lunarbook-pro-14')
            ->assertOk()
            ->assertSee('Ürün Açıklaması')
            ->assertSee('Yorumlar')
            ->assertSee('İptal ve İade Koşulları');
    }

    public function test_approved_reviews_are_visible_and_new_ones_require_approval(): void
    {
        $productId = Product::whereHas('urls', fn ($query) => $query->where('slug', 'lunarbook-pro-14'))->value('id');

        ProductReview::create([
            'product_id' => $productId,
            'name' => 'Test Yorumcu',
            'rating' => 5,
            'title' => 'Mükemmel',
            'body' => 'Onay bekleyen yorumun içeriği.',
            'is_approved' => false,
        ]);

        // Onaysız yorum vitrinde görünmez (Yorumlar sekmesi açılmış halde)
        Livewire::test(ProductPage::class, ['slug' => 'lunarbook-pro-14'])
            ->call('selectTab', 'reviews')
            ->assertDontSee('Onay bekleyen yorumun içeriği');

        // Yorum formu ile gönderim → onaysız kayıt oluşturur
        Livewire::test(ProductPage::class, ['slug' => 'lunarbook-pro-14'])
            ->set('reviewRating', 4)
            ->set('reviewerName', 'Test Kullanıcı')
            ->set('reviewTitle', 'Güzel')
            ->set('reviewBody', 'Çok kaliteli bir ürün, memnun kaldım.')
            ->call('submitReview')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $productId,
            'name' => 'Test Kullanıcı',
            'rating' => 4,
            'is_approved' => false,
        ]);

        // Yorum onaylanınca Yorumlar sekmesinde listelenir
        ProductReview::where('name', 'Test Yorumcu')->update(['is_approved' => true]);
        Livewire::test(ProductPage::class, ['slug' => 'lunarbook-pro-14'])
            ->call('selectTab', 'reviews')
            ->assertSee('Onay bekleyen yorumun içeriği');
    }
}
