<?php

namespace App\Livewire\Storefront;

use App\Livewire\Storefront\Concerns\InteractsWithCart;
use App\Livewire\Storefront\Concerns\InteractsWithWishlist;
use App\Support\StorefrontPricing;
use App\Support\StorefrontSearch;
use App\Support\WishlistManager;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Lunar\Models\Collection;
use Lunar\Models\Product;

#[Layout('layouts.storefront')]
class HomePage extends Component
{
    use InteractsWithCart;
    use InteractsWithWishlist;

    public array $products = [];

    public array $collections = [];

    /**
     * Header arama çubuğundan (`?q=`) gelen global arama terimi.
     * Ürün adı ve açıklamasında büyük/küçük harf duyarsız filtreleme yapar.
     */
    #[Url]
    public string $q = '';

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

        $this->applySearchFilter();

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

    /**
     * Header araması: ürün adı ve açıklamasında Türkçe-duyarlı filtreleme.
     */
    private function applySearchFilter(): void
    {
        if (trim($this->q) === '') {
            return;
        }

        $this->products = array_values(array_filter(
            $this->products,
            fn (array $product): bool => StorefrontSearch::matches(
                ($product['name'] ?? '').' '.($product['description'] ?? ''),
                $this->q
            )
        ));
    }
    public function render()
    {
        return view('livewire.storefront.home-page');
    }
}
