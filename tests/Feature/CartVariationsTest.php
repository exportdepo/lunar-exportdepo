<?php

namespace Tests\Feature;

use App\Livewire\Storefront\CartIndicator;
use App\Livewire\Storefront\CartPage;
use App\Livewire\Storefront\OrderStatusPage;
use App\Livewire\Storefront\ProductPage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Facades\CartSession;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Order;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;
use Tests\TestCase;

/**
 * Varyasyonlu ürünün sepete eklenmesi durumunda seçenek değerlerinin
 * sepet sayfası ve header sepet çekmecesinde görüntülenmesini; ayrıca sepet
 * sayfasındaki adet güncellemelerinin stok sınırlarına uyduğunu doğrular.
 */
class CartVariationsTest extends TestCase
{
    protected function tearDown(): void
    {
        CartSession::forget();

        parent::tearDown();
    }

    /**
     * Lunar doğrulayıcılarından dönen `CartException` 500 yerine Türkçe uyarı
     * olarak gösterilmelidir (ör. adet katı kuralı: 2'lik artışta 3 adet).
     */
    public function test_cart_page_surfaces_lunar_validation_errors_as_warning(): void
    {
        $product = $this->publishedProductWithVariant(stock: 10);
        $product->variants->first()->update(['quantity_increment' => 2]);

        Livewire::test(ProductPage::class, ['slug' => $product->urls->first()->slug])
            ->set('quantity', 2)
            ->call('addToCart');

        $line = CartSession::current()->lines->first();

        $component = Livewire::test(CartPage::class);
        $component->call('updateQuantity', $line->id, 3);

        $component->assertSee('Seçilen adet stok veya satış kurallarına uygun değil.')
            ->assertDispatched('cart-quantity-rejected');

        // Reddedilen adet sepete yazılmaz.
        $this->assertSame(2, (int) CartSession::current()->lines()->find($line->id)->quantity);
    }

    public function test_cart_page_updates_quantity_within_stock_limits(): void
    {
        $product = $this->publishedProductWithVariant(stock: 3);

        Livewire::test(ProductPage::class, ['slug' => $product->urls->first()->slug])
            ->call('addToCart');

        $line = CartSession::current()->lines->first();

        $component = Livewire::test(CartPage::class);
        $component->call('updateQuantity', $line->id, 3);

        $component->assertHasNoErrors();

        $this->assertSame(3, (int) CartSession::current()->lines()->find($line->id)->quantity);
    }

    public function test_cart_page_rejects_quantity_above_stock_with_turkish_warning(): void
    {
        $product = $this->publishedProductWithVariant(stock: 2);

        Livewire::test(ProductPage::class, ['slug' => $product->urls->first()->slug])
            ->call('addToCart');

        $line = CartSession::current()->lines->first();

        $component = Livewire::test(CartPage::class);
        $component->call('updateQuantity', $line->id, 99);

        // 500 yerine kullanıcıya Türkçe uyarı gösterilir ve stepper'ı
        // sunucudaki adete döndüren olay yayılır.
        $component->assertSee('Stokta en fazla 2 adet bulunuyor.')
            ->assertDispatched('cart-quantity-rejected');

        // Reddedilen adet sepete yazılmaz; satır önceki değerinde kalır.
        $this->assertSame(1, (int) CartSession::current()->lines()->find($line->id)->quantity);
    }

    public function test_cart_page_and_drawer_show_selected_variant_options(): void
    {
        $fixture = $this->productWithColourVariants();

        $component = Livewire::test(ProductPage::class, ['slug' => $fixture['slug']]);
        $component->call('selectOptionValue', $fixture['option']->id, $fixture['values']['Mavi']->id);
        $component->call('addToCart');

        Livewire::test(CartPage::class)
            ->assertSee('Renk: Mavi');

        Livewire::test(CartIndicator::class)
            ->assertSee('Renk: Mavi');
    }

    public function test_cart_page_hides_options_for_variant_without_options(): void
    {
        $product = $this->publishedProductWithVariant(stock: 5);

        Livewire::test(ProductPage::class, ['slug' => $product->urls->first()->slug])
            ->call('addToCart');

        Livewire::test(CartPage::class)
            ->assertDontSee('Renk:');
    }

    public function test_order_status_page_shows_variant_options(): void
    {
        $fixture = $this->productWithColourVariants();
        $variant = $fixture['variants']['Mavi'];

        $order = Order::factory()->create([
            'reference' => 'VAR-'.Str::ulid(),
            'status' => 'payment-received',
        ]);
        $order->lines()->create([
            'purchasable_type' => (new ProductVariant)->getMorphClass(),
            'purchasable_id' => $variant->id,
            'type' => 'physical',
            'description' => 'Test',
            'option' => null,
            'identifier' => $variant->sku,
            'quantity' => 1,
            'unit_price' => 500,
            'tax_total' => 0,
            'discount_total' => 0,
            'sub_total' => 500,
            'total' => 500,
            'tax_breakdown' => new TaxBreakdown,
        ]);

        Livewire::test(OrderStatusPage::class, ['reference' => $order->reference])
            ->assertSee('Renk: Mavi');
    }

    private function productWithColourVariants(): array
    {
        $context = $this->storefrontContext();
        $slug = 'varyasyon-sepet-'.Str::ulid();

        $product = Product::factory()->create(['status' => 'published']);
        $this->publishProduct($product, $context, $slug);

        $option = ProductOption::create([
            'handle' => 'renk-'.Str::ulid(),
            'name' => ['tr' => 'Renk', 'en' => 'Renk'],
            'shared' => false,
        ]);

        $values = [];
        foreach (['Siyah', 'Mavi'] as $index => $name) {
            $values[$name] = $option->values()->create([
                'name' => ['tr' => $name, 'en' => $name],
                'position' => $index + 1,
                'meta' => ['hex' => '#111827'],
            ]);
        }

        $variants = [];
        foreach ($values as $name => $value) {
            $variant = $product->variants()->create([
                'tax_class_id' => TaxClass::factory()->create()->id,
                'sku' => 'VAR-SEPET-'.Str::ulid(),
                'stock' => 5,
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'shippable' => true,
                'purchasable' => 'always',
            ]);
            $variant->values()->sync([$value->id]);
            $variant->prices()->create([
                'currency_id' => $context['currency']->id,
                'price' => 500,
                'min_quantity' => 1,
            ]);
            $variants[$name] = $variant;
        }
        $product->unsetRelation('variants');

        return [
            'product' => $product,
            'slug' => $slug,
            'option' => $option->fresh('values'),
            'values' => $values,
            'variants' => $variants,
        ];
    }

    private function publishedProductWithVariant(int $stock = 5): Product
    {
        $context = $this->storefrontContext();
        $slug = 'varyasyon-sepetsiz-'.Str::ulid();

        $product = Product::factory()->create(['status' => 'published']);
        $this->publishProduct($product, $context, $slug);

        $variant = $product->variants()->create([
            'tax_class_id' => TaxClass::factory()->create()->id,
            'sku' => 'VAR-SEPET-NOOPT-'.Str::ulid(),
            'stock' => $stock,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'always',
        ]);
        $variant->prices()->create([
            'currency_id' => $context['currency']->id,
            'price' => 500,
            'min_quantity' => 1,
        ]);
        $product->unsetRelation('variants');

        return $product->fresh(['variants.prices.currency', 'variants.values', 'urls', 'channels']);
    }

    private function storefrontContext(): array
    {
        TaxZone::query()->where('default', true)->exists() || TaxZone::factory()->create();

        $language = Language::factory()->create([
            'code' => 'tr-'.Str::ulid(),
            'default' => true,
        ]);

        Currency::query()->where('default', true)->update(['default' => false]);
        $currency = Currency::factory()->create([
            'code' => 'TRY'.Str::ulid(),
            'default' => true,
        ]);

        $channel = Channel::query()->firstOrCreate(
            ['handle' => 'web'],
            ['name' => 'Web', 'url' => config('app.url'), 'default' => true],
        );
        $channel->update(['default' => true]);

        CustomerGroup::query()->where('default', true)->update(['default' => false]);
        $customerGroup = CustomerGroup::query()->firstOrCreate(
            ['handle' => 'default'],
            ['name' => 'Standard', 'default' => true],
        );
        $customerGroup->update(['default' => true]);

        return ['language' => $language, 'currency' => $currency, 'channel' => $channel, 'customerGroup' => $customerGroup];
    }

    private function publishProduct(Product $product, array $context, string $slug): void
    {
        $product->channels()->syncWithoutDetaching([$context['channel']->id => ['enabled' => true]]);
        $product->customerGroups()->syncWithoutDetaching([
            $context['customerGroup']->id => ['enabled' => true, 'purchasable' => true],
        ]);
        $product->urls()->create([
            'language_id' => $context['language']->id,
            'slug' => $slug,
            'default' => true,
        ]);
    }
}
