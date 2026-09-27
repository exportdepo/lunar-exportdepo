<?php

namespace Tests\Concerns;

use Illuminate\Support\Str;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxZone;

/**
 * Vitrin testleri için yayında ürün + varyant + fiyat fikstürü üretir.
 *
 * Lunar'in satın alma akışı birden fazla varsayılana bağlıdır: varsayılan
 * para birimi, vergi sınıfı ve fiyat satırı olmadan sepete ekleme sessizce
 * başarısız olur. Bu trait bu zinciri tek yerde kurar.
 *
 * Ayrıca kanala ve müşteri grubuna bağlanır; aksi halde ürün vitrinde
 * görünmez veya satın alınamaz sayılır.
 */
trait CreatesStorefrontProduct
{
    protected function storefrontLanguage(): Language
    {
        return Language::factory()->create([
            'code' => 'tr-'.Str::ulid(),
            'default' => true,
        ]);
    }

    protected function publishedProductWithVariant(
        int $stock = 5,
        string $status = 'published',
        ?int $languageId = null
    ): Product {
        TaxZone::query()->where('default', true)->exists() || TaxZone::factory()->create();

        $languageId ??= $this->storefrontLanguage()->id;

        Currency::query()->where('default', true)->update(['default' => false]);
        $currency = Currency::factory()->create([
            'code' => 'TRY'.Str::ulid(),
            'default' => true,
        ]);

        $product = Product::factory()->create(['status' => $status]);

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

        $product->channels()->syncWithoutDetaching([
            $channel->id => ['enabled' => true],
        ]);
        $product->customerGroups()->syncWithoutDetaching([
            $customerGroup->id => ['enabled' => true, 'purchasable' => true],
        ]);
        $product->urls()->create([
            'language_id' => $languageId,
            'slug' => 'liste-urun-'.Str::ulid(),
            'default' => true,
        ]);

        $variant = $product->variants()->create([
            'tax_class_id' => TaxClass::factory()->create()->id,
            'sku' => 'LIST-'.Str::ulid(),
            'stock' => $stock,
            'unit_quantity' => 1,
            'min_quantity' => 1,
            'quantity_increment' => 1,
            'shippable' => true,
            'purchasable' => 'in_stock',
        ]);
        $variant->prices()->create([
            'currency_id' => $currency->id,
            'price' => 100,
            'min_quantity' => 1,
        ]);

        return $product->fresh(['variants.prices.currency', 'variants.values', 'urls', 'channels']);
    }
}
