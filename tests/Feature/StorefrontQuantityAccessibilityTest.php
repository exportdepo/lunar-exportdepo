<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\ProductPage;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Lunar\Models\Currency;
use Tests\Concerns\CreatesStorefrontProduct;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Adet (stepper) denetimlerinin erişilebilirlik sözleşmesini doğrular.
 *
 * Kapsam: spinbutton rolü ve değer/aralık nitelikleri (WCAG 4.1.2),
 * +/- düğmelerinde ok tuşu desteği (WCAG 2.1.1) ve hata mesajının
 * giriş alanına bağlanması (WCAG 3.3.1 / 4.1.3).
 */
class StorefrontQuantityAccessibilityTest extends TestCase
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

    public function test_product_page_quantity_input_exposes_spinbutton_semantics(): void
    {
        $xpath = $this->parseHtml($this->productPageHtml(stock: 7));

        $input = $this->firstNode($xpath, '//input[@id="quantity"]');

        $this->assertNotNull($input, 'Ürün sayfasındaki adet girişi bulunamadı.');
        $this->assertSame(
            'spinbutton',
            $input->getAttribute('role'),
            'Adet girişi spinbutton rolünü taşımalı.',
        );
        $this->assertSame('1', $input->getAttribute('aria-valuemin'));
        $this->assertSame(
            '7',
            $input->getAttribute(':aria-valuemax'),
            'Azami değer stokla (7) eşleşmeli.',
        );
        $this->assertStringContainsString(
            'Number(qty)',
            (string) $input->getAttribute(':aria-valuenow'),
            'Anlık değer, girişin gerçek değeriyle senkron olmalı.',
        );
    }

    public function test_cart_page_quantity_input_exposes_spinbutton_semantics(): void
    {
        $xpath = $this->parseHtml($this->cartPageHtml());

        $input = $this->firstNode($xpath, '//main//input[@type="number"]');

        $this->assertNotNull($input, 'Sepet sayfasındaki adet girişi bulunamadı.');
        $this->assertSame(
            'spinbutton',
            $input->getAttribute('role'),
            'Adet girişi spinbutton rolünü taşımalı.',
        );
        $this->assertSame('1', $input->getAttribute('aria-valuemin'));
        $this->assertSame(
            '99',
            $input->getAttribute('aria-valuemax'),
            'Azami değer, "+" düğmesinin sınırıyla (99) eşleşmeli.',
        );
        $this->assertSame(
            '99',
            $input->getAttribute('max'),
            'Yerel max niteliği de aynı sınırı bildirmeli.',
        );
        $this->assertStringContainsString(
            'Number(qty)',
            (string) $input->getAttribute(':aria-valuenow'),
            'Anlık değer, girişin gerçek değeriyle senkron olmalı.',
        );
    }

    public function test_product_page_quantity_value_is_omitted_while_the_field_is_empty(): void
    {
        $xpath = $this->parseHtml($this->productPageHtml(stock: 7));

        $input = $this->firstNode($xpath, '//input[@id="quantity"]');

        $this->assertNotNull($input, 'Adet girişi bulunamadı.');

        $expression = (string) $input->getAttribute(':aria-valuenow');

        // `aria-valuenow` gerçek değeri yansıtmalı (WCAG 4.1.2). Kullanıcı
        // alanı temizlediğinde Alpine modeli boş değere düşer; "1" değerine
        // düşmek görünmeyen bir değer bildirir. Değer yoksa nitelik
        // kaldırılmalı (Alpine `false`/`null`/`undefined` değerinde niteliği
        // DOM'dan siler).
        $this->assertDoesNotMatchRegularExpression(
            '/\|\|\s*1\b/',
            $expression,
            'Boş adet alanı "1" olarak bildirilmemeli (aria-valuenow yalanı).',
        );
        $this->assertMatchesRegularExpression(
            '/:\s*(false|null|undefined)\s*$/',
            trim($expression),
            'Geçerli sayı olmayan modelde aria-valuenow kaldırılmalı (false/null/undefined).',
        );
    }

    public function test_cart_page_quantity_value_is_omitted_while_the_field_is_empty(): void
    {
        $xpath = $this->parseHtml($this->cartPageHtml());

        $input = $this->firstNode($xpath, '//main//input[@type="number"]');

        $this->assertNotNull($input, 'Sepet sayfasındaki adet girişi bulunamadı.');

        $expression = (string) $input->getAttribute(':aria-valuenow');

        $this->assertDoesNotMatchRegularExpression(
            '/\|\|\s*1\b/',
            $expression,
            'Boş adet alanı "1" olarak bildirilmemeli (aria-valuenow yalanı).',
        );
        $this->assertMatchesRegularExpression(
            '/:\s*(false|null|undefined)\s*$/',
            trim($expression),
            'Geçerli sayı olmayan modelde aria-valuenow kaldırılmalı (false/null/undefined).',
        );
    }

    public function test_product_page_stepper_buttons_support_arrow_keys(): void
    {
        $xpath = $this->parseHtml($this->productPageHtml(stock: 7));

        $increase = $this->firstNode($xpath, '//button[@aria-label="Adedi artır"]');
        $decrease = $this->firstNode($xpath, '//button[@aria-label="Adedi azalt"]');

        $this->assertNotNull($increase, 'Adedi artır düğmesi bulunamadı.');
        $this->assertNotNull($decrease, 'Adedi azalt düğmesi bulunamadı.');

        // Ok tuşu, düğmenin kendi işlemini yapmalı (tıklamayla aynı).
        $this->assertTrue(
            $increase->hasAttribute('data-a11y-keydown.up.prevent'),
            '"Adedi artır" düğmesi yukarı ok tuşunu desteklemeli.',
        );
        $this->assertSame(
            $increase->getAttribute('data-a11y-click'),
            $increase->getAttribute('data-a11y-keydown.up.prevent'),
            'Yukarı ok tuşu, tıklama ile aynı işlemi yapmalı.',
        );

        $this->assertTrue(
            $decrease->hasAttribute('data-a11y-keydown.down.prevent'),
            '"Adedi azalt" düğmesi aşağı ok tuşunu desteklemeli.',
        );
        $this->assertSame(
            $decrease->getAttribute('data-a11y-click'),
            $decrease->getAttribute('data-a11y-keydown.down.prevent'),
            'Aşağı ok tuşu, tıklama ile aynı işlemi yapmalı.',
        );
    }

    public function test_cart_page_stepper_buttons_support_arrow_keys(): void
    {
        $xpath = $this->parseHtml($this->cartPageHtml());

        $increase = $this->firstNode($xpath, '//main//button[contains(@aria-label, "adet artır")]');
        $decrease = $this->firstNode($xpath, '//main//button[contains(@aria-label, "adet azalt")]');

        $this->assertNotNull($increase, 'Adet artır düğmesi bulunamadı.');
        $this->assertNotNull($decrease, 'Adet azalt düğmesi bulunamadı.');

        $this->assertTrue(
            $increase->hasAttribute('data-a11y-keydown.up.prevent'),
            '"Adet artır" düğmesi yukarı ok tuşunu desteklemeli.',
        );
        $this->assertSame(
            $increase->getAttribute('data-a11y-click'),
            $increase->getAttribute('data-a11y-keydown.up.prevent'),
            'Yukarı ok tuşu, tıklama ile aynı işlemi yapmalı.',
        );

        $this->assertTrue(
            $decrease->hasAttribute('data-a11y-keydown.down.prevent'),
            '"Adet azalt" düğmesi aşağı ok tuşunu desteklemeli.',
        );
        $this->assertSame(
            $decrease->getAttribute('data-a11y-click'),
            $decrease->getAttribute('data-a11y-keydown.down.prevent'),
            'Aşağı ok tuşu, tıklama ile aynı işlemi yapmalı.',
        );
    }

    public function test_product_page_quantity_error_is_described_by_the_input(): void
    {
        $product = $this->publishedProductWithVariant(stock: 7);
        $slug = $product->urls->first()->slug;

        // Stoktan fazla adet → "quantity" hata torbası dolar.
        $xpath = $this->parseHtml(
            Livewire::test(ProductPage::class, ['slug' => $slug])
                ->set('quantity', 99)
                ->call('addToCart')
                ->html(),
        );

        $input = $this->firstNode($xpath, '//input[@id="quantity"]');
        $error = $this->firstNode($xpath, '//p[@id="quantity-error"]');

        $this->assertNotNull($error, 'Adet hata mesajının kimliği olmalı.');
        $this->assertNotNull($input, 'Adet girişi bulunamadı.');
        $this->assertSame(
            'quantity-error',
            $input->getAttribute('aria-describedby'),
            'Hata mesajı, adet girişine bağlanmalı.',
        );
        $this->assertSame(
            'true',
            $input->getAttribute('aria-invalid'),
            'Hatalı adet girişi aria-invalid ile işaretlenmeli.',
        );
    }

    public function test_product_page_quantity_input_has_no_dangling_description_without_error(): void
    {
        $xpath = $this->parseHtml($this->productPageHtml(stock: 7));

        $input = $this->firstNode($xpath, '//input[@id="quantity"]');

        $this->assertNotNull($input, 'Adet girişi bulunamadı.');
        $this->assertSame(
            '',
            $input->getAttribute('aria-describedby'),
            'Hata yokken var olmayan bir öğeye işaret edilmemeli.',
        );
    }

    public function test_cart_page_rejected_quantity_is_described_by_that_line_input(): void
    {
        $product = $this->publishedProductWithVariant(stock: 2);
        $variant = $product->variants->first();

        CartSession::setChannel($variant->product->channels()->where('handle', 'web')->first());
        CartSession::manager()->add($variant, 1);

        $lineId = (int) CartSession::current()->lines->first()->id;

        // Stoktan fazla adet → adet reddedilir ve "cart" hatası oluşur.
        $xpath = $this->parseHtml(
            Livewire::test(CartPage::class)
                ->call('updateQuantity', $lineId, 9)
                ->html(),
        );

        $alert = $this->firstNode($xpath, '//*[@id="cart-error"]');

        $this->assertNotNull($alert, 'Sepet hatasının kimliği olmalı.');
        $this->assertStringContainsString('stok', strtolower($alert->textContent));

        // Not: Livewire bileşen denetimi yalnızca bileşenin kendi HTML'ini
        // üretir; düzen sarmalayıcısındaki `<main>` bu çıktıda bulunmaz.
        $input = $this->firstNode($xpath, '//input[@type="number"]');

        $this->assertNotNull($input, 'Sepet adet girişi bulunamadı.');
        $this->assertSame(
            'cart-error',
            $input->getAttribute('aria-describedby'),
            'Hata mesajı, reddedilen satırın adet girişine bağlanmalı.',
        );
        $this->assertSame(
            'true',
            $input->getAttribute('aria-invalid'),
            'Reddedilen adet girişi aria-invalid ile işaretlenmeli.',
        );
    }

    public function test_cart_page_marks_only_the_rejected_line_as_invalid(): void
    {
        $lineIds = $this->twoLineCart();

        $this->assertCount(2, $lineIds, 'Sepette iki satır bulunmalı.');

        // İkinci satırın adedi stoktan fazla → yalnızca ikinci satır reddedilir.
        $xpath = $this->parseHtml(
            Livewire::test(CartPage::class)
                ->call('updateQuantity', $lineIds[1], 9)
                ->html(),
        );

        $inputs = $xpath->query('//input[@type="number"]');

        $this->assertSame(2, $inputs->length, 'Sepette iki adet girişi olmalı.');

        $valid = $inputs->item(0);
        $rejected = $inputs->item(1);

        $this->assertNotNull($valid);
        $this->assertNotNull($rejected);

        // Geçerli satır hatalı işaretlenmemeli (WCAG 3.3.1): kullanıcıya
        // hangi satırı düzeltmesi gerektiği net söylenmelidir.
        $this->assertSame(
            '',
            $valid->getAttribute('aria-invalid'),
            'Reddedilmeyen satırın adet girişi aria-invalid ile işaretlenmemeli.',
        );
        $this->assertSame(
            '',
            $valid->getAttribute('aria-describedby'),
            'Reddedilmeyen satırın adet girişi hata mesajına bağlanmamalı.',
        );

        $this->assertSame(
            'true',
            $rejected->getAttribute('aria-invalid'),
            'Reddedilen satırın adet girişi aria-invalid ile işaretlenmeli.',
        );
        $this->assertSame(
            'cart-error',
            $rejected->getAttribute('aria-describedby'),
            'Reddedilen satırın adet girişi hata mesajına bağlanmalı.',
        );
    }

    public function test_cart_page_quantity_input_has_no_dangling_description_without_error(): void
    {
        $xpath = $this->parseHtml($this->cartPageHtml());

        $input = $this->firstNode($xpath, '//main//input[@type="number"]');

        $this->assertNotNull($input, 'Sepet adet girişi bulunamadı.');
        $this->assertSame(
            '',
            $input->getAttribute('aria-describedby'),
            'Hata yokken var olmayan bir öğeye işaret edilmemeli.',
        );
    }

    public function test_cart_page_clears_the_rejected_line_state_when_the_cart_disappears(): void
    {
        $lineIds = $this->twoLineCart();

        $component = Livewire::test(CartPage::class)
            ->call('updateQuantity', $lineIds[1], 9);

        $this->assertSame(
            $lineIds[1],
            $component->get('rejectedLineId'),
            'Reddedilen satır kaydedilmiş olmalı.',
        );

        // Sepet başka bir sekmede temizlenir → sonraki istekte sepet bulunmaz.
        CartSession::forget();

        $component->call('updateQuantity', $lineIds[0], 2);

        $this->assertNull(
            $component->get('rejectedLineId'),
            'Sepet kaybolduğunda reddedilen satır durumu temizlenmeli; aksi halde '
                .'görünmeyen bir hata mesajına bağlanan aria-invalid kalır.',
        );
    }

    /**
     * Ürün sayfasının HTML çıktısı.
     */
    private function productPageHtml(int $stock): string
    {
        $product = $this->publishedProductWithVariant(stock: $stock);

        return Livewire::test(ProductPage::class, ['slug' => $product->urls->first()->slug])->html();
    }

    /**
     * Sepet sayfasının HTML çıktısı (tek ürünlü sepet).
     */
    private function cartPageHtml(): string
    {
        $product = $this->publishedProductWithVariant(stock: 5);
        $variant = $product->variants->first();

        CartSession::setChannel($variant->product->channels()->where('handle', 'web')->first());
        CartSession::manager()->add($variant, 1);

        return $this->get('/sepet')->assertOk()->getContent();
    }

    /**
     * Farklı stoklu iki ürünlü sepet kurar ve satır kimliklerini döndürür.
     *
     * @return array<int, int>
     */
    private function twoLineCart(): array
    {
        $first = $this->publishedProductWithVariant(stock: 5);
        $second = $this->publishedProductWithVariant(stock: 2);

        // Her ürün çağrısı kendi para birimini varsayılan yapar. Sepet tek
        // para biriminde hesaplandığı için iki varyantın da güncel varsayılan
        // para biriminde fiyatı bulunmalı.
        $currency = Currency::query()->where('default', true)->firstOrFail();

        foreach ([$first, $second] as $product) {
            $variant = $product->variants->first();

            if (! $variant->prices()->where('currency_id', $currency->id)->exists()) {
                $variant->prices()->create([
                    'currency_id' => $currency->id,
                    'price' => 100,
                    'min_quantity' => 1,
                ]);
            }
        }

        CartSession::setChannel($first->channels()->where('handle', 'web')->first());

        CartSession::manager()->add($first->variants->first(), 1);
        CartSession::manager()->add($second->variants->first(), 1);

        return CartSession::current()->lines
            ->map(fn ($line): int => (int) $line->id)
            ->values()
            ->all();
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
}
