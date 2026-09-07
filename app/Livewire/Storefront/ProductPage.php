<?php

namespace App\Livewire\Storefront;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

#[Layout('layouts.storefront')]
class ProductPage extends Component
{
    public array $product = [];

    public string $slug = '';

    public int $quantity = 1;

    public bool $added = false;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $product = Product::with([
            'media',
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

        $variant = $product->variants->first();
        $price = $variant?->prices->first();
        $image = $product->media->first();

        $this->product = [
            'id' => $product->id,
            'name' => (string) ($product->translateAttribute('name') ?? ''),
            'description' => (string) ($product->translateAttribute('description') ?? ''),
            'image_url' => $image?->getUrl(),
            'price' => $price?->price?->value,
            'stock' => $variant?->stock,
            'variant_id' => $variant?->id,
            'collection' => $product->collections->first()?->translateAttribute('name'),
            'collection_url' => $product->collections->first()?->urls->first()?->slug,
        ];
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

        CartSession::manager()->add(
            $variant,
            $this->quantity,
        );

        $this->added = true;
        $this->dispatch('cart-updated');
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

    public function render()
    {
        return view('livewire.storefront.product-page');
    }
}
