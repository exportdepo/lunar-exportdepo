<?php

namespace App\Livewire\Storefront;

use App\Livewire\Storefront\Concerns\InteractsWithWishlist;
use App\Support\StorefrontPricing;
use App\Support\WishlistManager;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Collection;
use Lunar\Models\Product;

#[Layout('layouts.storefront')]
class HomePage extends Component
{
    use InteractsWithWishlist;

    public array $products = [];

    public array $collections = [];

    public function mount()
    {
        $products = Product::with([
            'media',
            'variants.prices.currency',
            'urls',
        ])
            ->where('status', 'published')
            ->get();

        $this->products = $products->map(function (Product $product): array {
            $variant = $product->variants->first();
            $price = $variant ? StorefrontPricing::forVariantInclTax($variant) : null;
            $image = $product->media->first();
            $url = $product->urls->first();

            return [
                'id' => $product->id,
                'name' => (string) ($product->translateAttribute('name') ?? ''),
                'description' => (string) ($product->translateAttribute('description') ?? ''),
                'url' => $url?->slug,
                'image_url' => $image?->getUrl(),
                'stock' => $variant?->stock,
                'price' => $price,
            ];
        })->all();

        $collections = Collection::with(['media', 'urls'])
            ->orderBy('created_at')
            ->get();

        $this->collections = $collections->map(function (Collection $collection): array {
            $image = $collection->media->first();
            $url = $collection->urls->first();

            return [
                'id' => $collection->id,
                'name' => (string) ($collection->translateAttribute('name') ?? ''),
                'description' => (string) ($collection->translateAttribute('description') ?? ''),
                'url' => $url?->slug,
                'image_url' => $image?->getUrl(),
            ];
        })->all();

        $this->wishlistIds = WishlistManager::ids();
    }

    public function render()
    {
        return view('livewire.storefront.home-page');
    }
}
