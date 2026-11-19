<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CartIndicator;
use App\Support\WishlistManager;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Tests\Concerns\CreatesStorefrontProduct;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Header'daki sepet ve istek listesi göstergelerinin erişilebilirlik
 * sözleşmesini doğrular.
 *
 * Kapsam: ikon-only düğmelerde 44×44px dokunma hedefi (WCAG 2.5.5) ve
 * sepet ürün sayısı değişikliklerinin kalıcı canlı bölge ile duyurulması
 * (WCAG 4.1.3).
 */
class StorefrontIndicatorAccessibilityTest extends TestCase
{
    use CreatesStorefrontProduct;
    use SeedsLunarDefaults;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLunarDefaults();
    }

    protected function tearDown(): void
    {
        CartSession::forget();

        parent::tearDown();
    }

    public function test_wishlist_indicator_has_a_44px_touch_target(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $wishlist = $this->firstNode($xpath, '//a[starts-with(@aria-label, "İstek Listem")]');

        $this->assertNotNull($wishlist, 'İstek listesi göstergesi bulunamadı.');
        $this->assertStringContainsString(
            'h-11',
            $wishlist->getAttribute('class'),
            'İstek listesi göstergesi 44px yüksekliğinde olmalı (h-11).',
        );
        $this->assertStringContainsString(
            'w-11',
            $wishlist->getAttribute('class'),
            'İstek listesi göstergesi 44px genişliğinde olmalı (w-11).',
        );
    }

    public function test_cart_indicator_has_a_44px_touch_target(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $cart = $this->firstNode($xpath, '//button[@id="cart-trigger-button"]');

        $this->assertNotNull($cart, 'Sepet göstergesi bulunamadı.');
        $this->assertStringContainsString(
            'h-11',
            $cart->getAttribute('class'),
            'Sepet göstergesi 44px yüksekliğinde olmalı (h-11).',
        );
    }

    public function test_cart_indicator_has_a_persistent_live_region(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $regions = $xpath->query('//*[@id="cart-live-region"]');

        $this->assertGreaterThan(
            0,
            $regions->length,
            'Sepet göstergesinde kalıcı canlı bölge bulunmalı.',
        );

        $region = $regions->item(0);

        $this->assertSame('status', $region->getAttribute('role'));
        $this->assertSame('polite', $region->getAttribute('aria-live'));
    }

    public function test_cart_live_region_is_empty_on_first_render(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $region = $this->firstNode($xpath, '//*[@id="cart-live-region"]');

        $this->assertNotNull($region, 'Canlı bölge bulunamadı.');
        $this->assertSame(
            '',
            trim($region->textContent),
            'Sayfa ilk yüklendiğinde canlı bölge boş olmalı (gereksiz duyuru yapılmamalı).',
        );
    }

    public function test_cart_update_announces_the_new_item_count(): void
    {
        $product = $this->publishedProductWithVariant(stock: 5);
        $variant = $product->variants->first();

        CartSession::setChannel($variant->product->channels()->where('handle', 'web')->first());

        // Bileşen boş sepetle başlar: ilk yüklemede duyuru yapılmaz.
        $component = Livewire::test(CartIndicator::class);

        $before = $this->firstNode($this->parseHtml($component->html()), '//*[@id="cart-live-region"]');

        $this->assertNotNull($before, 'Canlı bölge bulunamadı.');
        $this->assertSame(
            '',
            trim($before->textContent),
            'İlk yüklemede canlı bölge boş olmalı.',
        );

        // Sepete ürün eklenir; `cart-updated` olayı metni günceller.
        CartSession::manager()->add($variant, 2);

        $xpath = $this->parseHtml($component->call('refreshCart')->html());

        $region = $this->firstNode($xpath, '//*[@id="cart-live-region"]');

        $this->assertNotNull($region, 'Canlı bölge bulunamadı.');
        $this->assertStringContainsString(
            '2',
            $region->textContent,
            'Canlı bölge yeni ürün sayısını bildirmeli.',
        );
    }

    public function test_cart_updated_event_announces_the_new_item_count(): void
    {
        $product = $this->publishedProductWithVariant(stock: 5);
        $variant = $product->variants->first();

        CartSession::setChannel($variant->product->channels()->where('handle', 'web')->first());

        $component = Livewire::test(CartIndicator::class);

        CartSession::manager()->add($variant, 2);

        $component->dispatch('cart-updated')
            ->assertSet('count', 2)
            ->assertSet('cartAnnouncement', 'Sepetinizde 2 ürün var.');
    }

    public function test_cart_indicator_accessible_name_includes_the_item_count(): void
    {
        $product = $this->publishedProductWithVariant(stock: 5);
        $variant = $product->variants->first();

        CartSession::setChannel($variant->product->channels()->where('handle', 'web')->first());
        CartSession::manager()->add($variant, 2);

        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $cart = $this->firstNode($xpath, '//button[@id="cart-trigger-button"]');

        $this->assertNotNull($cart, 'Sepet göstergesi bulunamadı.');

        // Rozet üzerindeki sayı `aria-label` ile gizlenir; ekran okuyucu
        // kullanıcısına sayıyı ancak erişilebilir adda iletebiliriz.
        $label = (string) $cart->getAttribute('aria-label');

        $this->assertStringContainsString(
            '2',
            $label,
            'Sepet düğmesinin erişilebilir adı ürün sayısını içermeli.',
        );
        $this->assertStringContainsString(
            'ürün',
            $label,
            'Sepet düğmesinin erişilebilir adı sayının birimini içermeli.',
        );
    }

    public function test_wishlist_indicator_accessible_name_includes_the_item_count(): void
    {
        $product = $this->publishedProductWithVariant(stock: 5);

        WishlistManager::add($product->id);

        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $wishlist = $this->firstNode($xpath, '//a[starts-with(@aria-label, "İstek Listem")]');

        $this->assertNotNull($wishlist, 'İstek listesi göstergesi bulunamadı.');

        $label = (string) $wishlist->getAttribute('aria-label');

        $this->assertStringContainsString(
            '1',
            $label,
            'İstek listesi bağlantısının erişilebilir adı ürün sayısını içermeli.',
        );
        $this->assertStringContainsString(
            'ürün',
            $label,
            'İstek listesi bağlantısının erişilebilir adı sayının birimini içermeli.',
        );
    }

    private function parseHtml(string $html): DOMXPath
    {
        $document = new DOMDocument;

        // Alpine'ın `@` ile başlayan nitelikleri HTML ayrıştırıcı tarafından
        // düşürülür; denetim yapılabilsin diye `data-a11y-` önekiyle
        // yeniden adlandırılır.
        $html = preg_replace('/(\s)@([a-zA-Z][\w.:-]*)=/', '$1data-a11y-$2=', $html) ?? $html;

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function firstNode(DOMXPath $xpath, string $query): ?DOMElement
    {
        $nodes = $xpath->query($query);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }
}
