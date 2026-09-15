<?php

namespace App\Livewire\Storefront;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\Channel;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

#[Layout('layouts.storefront')]
class ProductPage extends Component
{
    public array $product = [];

    public array $variants = [];

    public ?int $selectedVariantId = null;

    public string $slug = '';

    public int $quantity = 1;

    public bool $added = false;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $product = Product::with([
            'media',
            'variants.values',
            'variants.prices.currency',
            'urls',
            'collections.urls',
        ])
            ->where('status', 'published')
            ->whereHas('urls', fn ($query) => $query->where('slug', $slug))
            ->first();

        if (! $product) {
            throw (new ModelNotFoundException)->setModel(Product::class, [$slug]);
        }

        $image = $product->media->first();

        $this->product = [
            'id' => $product->id,
            'name' => (string) ($product->translateAttribute('name') ?? ''),
            'description' => (string) ($product->translateAttribute('description') ?? ''),
            'image_url' => $image?->getUrl(),
            'price' => null,
            'stock' => null,
            'variant_id' => null,
            'collection' => $product->collections->first()?->translateAttribute('name'),
            'collection_url' => $product->collections->first()?->urls->first()?->slug,
        ];

        $this->variants = $this->getProductVariants($product);

        $this->selectedVariantId = $product->variants->first()?->id;
        $this->updateSelectedVariant($product->variants->first());
    }

    protected function getProductVariants(Product $product): array
    {
        return $product->variants->map(fn (ProductVariant $variant): array => [
            'id' => $variant->id,
            'label' => $variant->getOption() ?: $variant->sku ?: 'Varyant ' . $variant->id,
        ])->values()->all();
    }

    public function updatedSelectedVariantId(?int $variantId): void
    {
        $variant = collect($this->variants)->firstWhere('id', $variantId);

        if (! $variant) {
            return;
        }

        $this->product['variant_id'] = $variantId;
        $this->updateSelectedVariant($this->productVariant());
        $this->added = false;
    }

    public function addToCart(): void
    {
        $this->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if (! $this->product['variant_id']) {
            $this->addError('quantity', 'Bu ürün şu anda satın alınamıyor.');
            return;
        }

        $variant = $this->productVariant();

        if ($variant->stock !== null && $this->quantity > $variant->stock) {
            $this->addError('quantity', 'Seçilen adet mevcut stoktan fazla.');
            return;
        }

        $channel = $variant->product->channels()
            ->where('handle', 'web')
            ->first();

        if ($channel instanceof Channel) {
            CartSession::setChannel($channel);
        }

        CartSession::manager()->add(
            $variant,
            $this->quantity,
        );

        $this->added = true;
        $this->dispatch('cart-updated')->to(CartIndicator::class);
    }

    protected function productVariant()
    {
        return ProductVariant::query()
            ->whereKey($this->product['variant_id'])
            ->whereHas('product', function ($query) {
                $query
                    ->where('status', 'published')
                    ->whereHas('urls', fn ($urlQuery) => $urlQuery->where('slug', $this->slug));
            })
            ->firstOrFail();
    }

    public function updatedQuantity(): void
    {
        if (! $this->product['variant_id'] || $this->quantity < 1) {
            return;
        }

        // Adet değişince birim fiyatı yeniden çözümle (miktar kademesi olabilir).
        $this->updateSelectedVariant($this->productVariant());
    }

    protected function updateSelectedVariant(?ProductVariant $variant): void
    {
        if (! $variant) {
            return;
        }

        $this->product['price'] = \App\Support\StorefrontPricing::forVariantInclTax($variant, $this->quantity);
        $this->product['stock'] = $variant->stock;
        $this->product['variant_id'] = $variant->id;
    }

    public function render()
    {
        return view('livewire.storefront.product-page');
    }
}
