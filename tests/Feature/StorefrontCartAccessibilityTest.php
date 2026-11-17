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

    /**
     * İstek listesi bildiriminin kendi canlı bölgesi: kalıcı `role="status"`
     * kapsayıcısı. Sayfadaki başka bir status düğümü bu denetimi yanlışlıkla
     * geçirmemeli.
     */
    private const WISHLIST_NOTICE_REGION = '//*[@id="wishlist-cart-alert"]'
        .'[@role="status" and @aria-live="polite"]';

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
        $this->assertStringContainsString(
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

    public function test_cart_preview_popup_is_inside_the_focus_handler_wrapper(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        // Odak sarmalayıcısına girince kart açılır; karttan çıkınca kapanır.
        // Bu "focusout sonra focusin" iptali, kartın sarmalayıcının İÇİNDE
        // olmasına bağlıdır. Kart dışarı taşınırsa bu sözleşme bozulur.
        $this->assertSame(
            1,
            $this->countNodes($xpath, '//*[@data-a11y-focusin][.//*[@id="header-cart-popup"]]'),
            'Önizleme kartı, odak işleyicilerinin bulunduğu sarmalayıcının içinde olmalı.',
        );
    }

    public function test_cart_preview_does_not_open_while_the_cart_drawer_is_open(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $wrapper = $this->firstNode($xpath, '//*[@data-a11y-mouseenter="showPopup()"]');

        $this->assertNotNull($wrapper, 'Sepet önizleme sarmalayıcısı bulunamadı.');

        $focusin = $wrapper->getAttribute('data-a11y-focusin');

        // Çekmece açıldığında Alpine paneli odaklar; bu odak olayı sarmalayıcıya
        // yayılır ve masaüstü önizleme kartını modalın arkasında açar.
        $this->assertStringContainsString(
            '$store.cartDrawer.open',
            $focusin,
            'Çekmece açıkken önizleme kartı açılmamalı.',
        );
        $this->assertStringContainsString(
            'showPopup()',
            $focusin,
            'Sepet önizleme kartı odaklanıldığında açılmalı.',
        );
    }

    public function test_cart_preview_escape_returns_focus_to_the_trigger(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $wrapper = $this->firstNode($xpath, '//*[@data-a11y-mouseenter="showPopup()"]');

        $this->assertNotNull($wrapper, 'Sepet önizleme sarmalayıcısı bulunamadı.');

        $escape = $wrapper->getAttribute('data-a11y-keydown.escape.window');

        $this->assertNotNull($escape, 'Escape işleyicisi bulunamadı.');
        $this->assertStringContainsString(
            'cartTrigger.focus()',
            $escape,
            'Escape ile önizleme kartı kapanınca odak tetikleyiciye dönmeli.',
        );
        $this->assertStringContainsString(
            'if (open)',
            $escape,
            'Escape yalnızca önizleme kartı açıkken odak çalmalı.',
        );
    }

    public function test_cart_preview_escape_closes_the_popup_before_restoring_focus(): void
    {
        $xpath = $this->parseHtml($this->get('/')->assertOk()->getContent());

        $wrapper = $this->firstNode($xpath, '//*[@data-a11y-mouseenter="showPopup()"]');

        $this->assertNotNull($wrapper, 'Sepet önizleme sarmalayıcısı bulunamadı.');

        $escape = $wrapper->getAttribute('data-a11y-keydown.escape.window');

        $this->assertNotNull($escape, 'Escape işleyicisi bulunamadı.');

        // Odak tetikleyiciye DÖNÜŞÜ, kart kapatıldıktan SONRA olmalı.
        // Ters sırada olursa `.focus()` gerçek bir focusin olayı üretir,
        // `@focusin` işleyicisi `showPopup()` çağırır ve kart yeniden açılır:
        // Escape "kartı kapatma" işlevini yerine getirmez.
        $focusPosition = strpos($escape, 'cartTrigger.focus()');
        $closePosition = strpos($escape, 'open = false');

        $this->assertNotFalse($focusPosition, 'Odak geri alma çağrısı bulunamadı.');
        $this->assertNotFalse($closePosition, 'Kapatma çağrısı bulunamadı.');
        $this->assertLessThan(
            $closePosition,
            $focusPosition,
            'Escape işleyicisinde önce odak tetikleyiciye dönmeli, sonra kart kapatılmalı '
            .'(aksi halde focusin olayı kartı yeniden açar).',
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

        // Bildirimin kendi canlı bölgesi: içinde `wishlist-cart-alert-`
        // anahtarlı bildirim kutusu bulunan bölge. Sayfadaki başka bir
        // status düğümü bu denetimi yanlışlıkla geçirmemeli.
        $regions = $xpath->query(self::WISHLIST_NOTICE_REGION);

        $this->assertGreaterThan(
            0,
            $regions->length,
            'İstek listesi sepet bildirimi ekran okuyucuya duyurulmuyor.',
        );

        $announced = false;

        foreach ($regions as $region) {
            if (str_contains($region->textContent, 'Ürün sepete eklendi')) {
                $announced = true;
                break;
            }
        }

        $this->assertTrue(
            $announced,
            'Bildirim metni, canlı bölgenin içinde bulunmalı.',
        );
    }

    public function test_wishlist_cart_notice_lives_in_a_persistent_live_region(): void
    {
        $product = $this->publishedProductWithVariant(stock: 3);

        session([WishlistManager::SESSION_KEY => [$product->id]]);

        // Bildirim gösterilmeden önce de canlı bölge DOM'da bulunmalı; bölge
        // ve içerik aynı anda eklenirse ekran okuyucu duyurmaz (WCAG 4.1.3).
        $before = $this->parseHtml(Livewire::test(WishlistPage::class)->html());

        $regionsBefore = $this->countNodes($before, self::WISHLIST_NOTICE_REGION);

        $this->assertGreaterThan(
            0,
            $regionsBefore,
            'Canlı bölge, bildirim gösterilmeden önce de DOM içinde bulunmalı.',
        );

        $after = $this->parseHtml(
            Livewire::test(WishlistPage::class)
                ->set('addedProductId', $product->id)
                ->html(),
        );

        $this->assertSame(
            $regionsBefore,
            $this->countNodes($after, self::WISHLIST_NOTICE_REGION),
            'Bildirim gösterilirken canlı bölge sayısı değişmemeli (bölge kalıcı olmalı).',
        );
    }

    public function test_product_card_wishlist_button_names_the_product(): void
    {
        $button = $this->productCardWishlistButton(false);

        $this->assertNotNull($button, 'İstek listesi düğmesi bulunamadı.');
        $this->assertStringContainsString('Kablosuz Klavye', $button->getAttribute('aria-label'));
        $this->assertStringContainsString('Kablosuz Klavye', $button->getAttribute('title'));
    }

    public function test_product_card_wishlist_button_names_the_product_when_already_saved(): void
    {
        $button = $this->productCardWishlistButton(true);

        $this->assertNotNull($button, 'İstek listesi düğmesi bulunamadı.');
        $this->assertStringContainsString(
            'İstek listesinden çıkar',
            $button->getAttribute('aria-label'),
        );
        $this->assertStringContainsString(
            'İstek listesinden çıkar',
            $button->getAttribute('title'),
        );
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

        // Denetimler `<main>` içine SADELEŞTİRİLMELİ: header'daki önizleme
        // kartı da "removeLine" düğmesi taşır. Sayfa kapsamı belirtilmezse
        // sepet sayfasının kaldırma düğmesi hiç denetlenmemiş olur.
        $heading = $this->firstNode($xpath, '//main//li//h2');

        $this->assertNotNull($heading, 'Sepet satırı başlığı bulunamadı.');

        $productName = trim($heading->textContent);

        $remove = $this->firstNode($xpath, '//main//button[contains(@*[name()="wire:click"], "removeLine")]');

        $this->assertNotNull($remove, 'Sepetten kaldırma düğmesi bulunamadı.');
        $this->assertStringContainsString($productName, $remove->getAttribute('aria-label'));

        $decrease = $this->firstNode($xpath, '//main//button[contains(@aria-label, "adet azalt")]');

        $this->assertNotNull($decrease, 'Adet azaltma düğmesi bulunamadı.');
        $this->assertStringContainsString($productName, $decrease->getAttribute('aria-label'));
    }

    /**
     * Ürün kartındaki istek listesi düğmesini döndürür.
     */
    private function productCardWishlistButton(bool $inWishlist): ?DOMElement
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
            '<x-product-card :product="$product" :show-wishlist="true" :in-wishlist="'.($inWishlist ? 'true' : 'false').'" />',
            compact('product'),
        ));

        return $this->firstNode($xpath, '//button[contains(@*[name()="wire:click"], "toggle")]');
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
