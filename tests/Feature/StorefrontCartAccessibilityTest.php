<?php

namespace Tests\Feature;

use App\Livewire\Storefront\WishlistPage;
use App\Support\WishlistManager;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Tests\Concerns\CreatesStorefrontProduct;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Sepet, istek listesi ve ürün kartı yüzeylerinin erişilebilirlik
 * gereksinimlerini doğrular.
 *
 * Kapsam: klavyeyle açılabilen sepet önizleme kartı (WCAG 2.1.1), ekran
 * okuyucuya duyurulan bildirimler (WCAG 4.1.3) ve hangi ürüne ait oldukları
 * belirtilen düğme etiketleri (WCAG 4.1.2).
 */
class StorefrontCartAccessibilityTest extends TestCase
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

    public function test_cart_preview_opens_on_keyboard_focus(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $wrapper = $this->firstNode($xpath, '//*[@data-a11y-mouseenter="showPopup()"]');

        $this->assertNotNull($wrapper, 'Sepet önizleme sarmalayıcısı bulunamadı.');
        $this->assertSame(
            'showPopup()',
            $wrapper->getAttribute('data-a11y-focusin'),
            'Sepet önizleme kartı odaklanıldığında açılmıyor.',
        );
        $this->assertSame(
            'scheduleHidePopup()',
            $wrapper->getAttribute('data-a11y-focusout'),
            'Sepet önizleme kartı odak kaybında kapanmıyor.',
        );
    }

    public function test_wishlist_cart_notice_is_announced_to_screen_readers(): void
    {
        $product = $this->publishedProductWithVariant(stock: 3);

        // Bildirim yalnızca listede ürün varken basılır.
        session([WishlistManager::SESSION_KEY => [$product->id]]);

        $html = Livewire::test(WishlistPage::class)
            ->set('addedProductId', $product->id)
            ->html();

        $xpath = $this->parseHtml($html);

        $this->assertGreaterThan(
            0,
            $this->countNodes($xpath, '//*[@role="status" and @aria-live="polite"]'),
            'İstek listesi sepet bildirimi ekran okuyucuya duyurulmuyor.',
        );
    }

    public function test_product_card_wishlist_button_names_the_product(): void
    {
        $product = [
            'id' => 42,
            'name' => 'Kablosuz Klavye',
            'description' => 'Açıklama',
            'price' => 1999,
            'stock' => 3,
            'image_url' => null,
            'url' => 'kablosuz-klavye',
        ];

        $xpath = $this->parseHtml(Blade::render(
            '<x-product-card :product="$product" :show-wishlist="true" :in-wishlist="false" />',
            compact('product'),
        ));

        $button = $this->firstNode($xpath, '//button[contains(@*[name()="wire:click"], "toggle")]');

        $this->assertNotNull($button, 'İstek listesi düğmesi bulunamadı.');
        $this->assertStringContainsString('Kablosuz Klavye', $button->getAttribute('aria-label'));
        $this->assertStringContainsString('Kablosuz Klavye', $button->getAttribute('title'));
    }

    public function test_cart_page_buttons_name_the_product(): void
    {
        $product = $this->publishedProductWithVariant(stock: 5);

        $variant = $product->variants->first();

        CartSession::setChannel($variant->product->channels()->where('handle', 'web')->first());
        CartSession::manager()->add($variant, 1);

        $xpath = $this->parseHtml($this->get('/sepet')->assertOk()->getContent());

        $heading = $this->firstNode($xpath, '//li//h2');

        $this->assertNotNull($heading, 'Sepet satırı başlığı bulunamadı.');

        $productName = trim($heading->textContent);

        $remove = $this->firstNode($xpath, '//button[contains(@*[name()="wire:click"], "removeLine")]');

        $this->assertNotNull($remove, 'Sepetten kaldırma düğmesi bulunamadı.');
        $this->assertStringContainsString($productName, $remove->getAttribute('aria-label'));

        $decrease = $this->firstNode($xpath, '//button[contains(@aria-label, "adet azalt")]');

        $this->assertNotNull($decrease, 'Adet azaltma düğmesi bulunamadı.');
        $this->assertStringContainsString($productName, $decrease->getAttribute('aria-label'));
    }

    private function parseHtml(string $html): DOMXPath
    {
        $document = new DOMDocument;

        // Alpine'ın `@` ile başlayan nitelikleri (`:focusin` gibi) HTML
        // ayrıştırıcı tarafından düşürülür; denetim yapılabilsin diye
        // nitelik adları geçici olarak `data-a11y-` önekiyle yeniden adlandırılır.
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

    private function countNodes(DOMXPath $xpath, string $query): int
    {
        $nodes = $xpath->query($query);

        return $nodes === false ? 0 : $nodes->length;
    }
}
