<?php

namespace Tests\Feature;

use App\Livewire\Storefront\HomePage;
use App\Livewire\Storefront\ProductPage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Facades\CartSession;
use Lunar\FieldTypes\Text;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\TaxClass;
use Tests\Concerns\SeedsLunarDefaults;
use Tests\TestCase;

/**
 * Vitrin ürün ve ödeme formlarının erişilebilirlik sözleşmesini doğrular.
 */
class StorefrontProductAccessibilityTest extends TestCase
{
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

    public function test_checkout_payment_options_are_grouped_with_a_named_legend(): void
    {
        $fixture = $this->publishedProductWithOptions();

        Livewire::test(HomePage::class)->call('addToCart', $fixture['product']->id);

        config([
            'lunar.payments.types.stripe.secret' => 'sk_test_accessibility',
            'services.stripe.webhook_secret' => 'whsec_accessibility',
        ]);

        $this->get('/checkout')
            ->assertOk()
            ->assertSee('<fieldset class="mt-4 space-y-3">', false)
            ->assertSee('<legend class="sr-only">Ödeme Yöntemi</legend>', false)
            ->assertSee('value="offline"', false)
            ->assertSee('value="stripe"', false);
    }

    public function test_product_option_groups_render_as_named_fieldsets(): void
    {
        $fixture = $this->publishedProductWithOptions();

        $component = Livewire::test(ProductPage::class, ['slug' => $fixture['slug']]);

        $component->assertSee('<legend class="sr-only">Ürün seçenekleri</legend>', false)
            ->assertSee('<fieldset class="min-w-0">', false)
            ->assertSee('<legend class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">', false)
            ->assertSee('Renk', false)
            ->assertSee('Beden', false);

        $this->assertSame(2, substr_count($component->html(), '<fieldset class="min-w-0">'));
    }

    public function test_colour_swatch_accessible_name_includes_colour_and_hex_value(): void
    {
        $fixture = $this->publishedProductWithOptions();

        Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
            ->assertSee('aria-label="Renk: Siyah (#111827)"', false)
            ->assertSee('aria-label="Renk: Beyaz (#f1f5f9) (stokta yok)"', false);
    }

    public function test_product_status_messages_are_announced_politely(): void
    {
        $fixture = $this->publishedProductWithOptions();

        Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
            ->set('added', true)
            ->set('activeTab', 'reviews')
            ->set('reviewSubmitted', true)
            ->assertSeeHtml('<div role="status" aria-live="polite" class="mt-4 flex flex-wrap items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">')
            ->assertSeeHtml('<div role="status" aria-live="polite" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">');
    }

    public function test_product_wishlist_button_includes_product_name(): void
    {
        $fixture = $this->publishedProductWithOptions();

        Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
            ->assertSee('title="İstek listesine ekle: Erişilebilirlik Test Ürünü"', false)
            ->assertSee('aria-label="İstek listesine ekle: Erişilebilirlik Test Ürünü"', false);
    }

    public function test_product_breadcrumb_marks_the_current_page(): void
    {
        $fixture = $this->publishedProductWithOptions();

        Livewire::test(ProductPage::class, ['slug' => $fixture['slug']])
            ->assertSee('<li aria-current="page" class="max-w-full truncate font-medium text-slate-700 dark:text-slate-200">Erişilebilirlik Test Ürünü</li>', false);
    }

    /**
     * @return array{product: Product, slug: string}
     */
    private function publishedProductWithOptions(): array
    {
        $language = Language::query()->where('default', true)->firstOrFail();
        $currency = Currency::query()->where('default', true)->firstOrFail();
        $channel = Channel::query()->where('default', true)->firstOrFail();
        $customerGroup = CustomerGroup::query()->where('default', true)->firstOrFail();
        $taxClass = TaxClass::factory()->create();
        $slug = 'erisilebilirlik-test-'.Str::ulid();

        $product = Product::factory()->create(['status' => 'published']);
        $product->update([
            'attribute_data' => collect(['name' => new Text('Erişilebilirlik Test Ürünü')]),
        ]);

        $product->channels()->syncWithoutDetaching([$channel->id => ['enabled' => true]]);
        $product->customerGroups()->syncWithoutDetaching([
            $customerGroup->id => ['enabled' => true, 'purchasable' => true],
        ]);
        $product->urls()->create([
            'language_id' => $language->id,
            'slug' => $slug,
            'default' => true,
        ]);

        $renk = ProductOption::create([
            'handle' => 'renk-'.Str::ulid(),
            'name' => ['tr' => 'Renk', 'en' => 'Renk'],
            'shared' => false,
        ]);
        $siyah = $renk->values()->create([
            'name' => ['tr' => 'Siyah', 'en' => 'Siyah'],
            'position' => 1,
            'meta' => ['hex' => '#111827'],
        ]);
        $renk->values()->create([
            'name' => ['tr' => 'Beyaz', 'en' => 'Beyaz'],
            'position' => 2,
            'meta' => ['hex' => '#f1f5f9'],
        ]);

        $beden = ProductOption::create([
            'handle' => 'beden-'.Str::ulid(),
            'name' => ['tr' => 'Beden', 'en' => 'Beden'],
            'shared' => false,
        ]);
        $small = $beden->values()->create([
            'name' => ['tr' => 'S', 'en' => 'S'],
            'position' => 1,
        ]);

        $product->productOptions()->sync([
            $renk->id => ['position' => 1],
            $beden->id => ['position' => 2],
        ]);

        $variant = $product->variants()->create([
            'tax_class_id' => $taxClass->id,
            'sku' => 'ACCESSIBILITY-'.Str::ulid(),
            'stock' => 5,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'always',
        ]);
        $variant->values()->sync([$siyah->id, $small->id]);
        $variant->prices()->create([
            'currency_id' => $currency->id,
            'price' => 500,
            'min_quantity' => 1,
        ]);

        return [
            'product' => $product,
            'slug' => $slug,
        ];
    }
}
