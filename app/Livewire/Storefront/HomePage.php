<?php

namespace App\Livewire\Storefront;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Product;
use Lunar\Models\Collection;

#[Layout('layouts.storefront')]
class HomePage extends Component
{
    /** @var array */
    public array $products = [];

    /** @var array */
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
            $price = $variant ? \App\Support\StorefrontPricing::forVariantInclTax($variant) : null;
            $image = $product->media->first();
            $url = $product->urls->first();

            return [
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
    }

    public function render()
    {
        return view('livewire.storefront.home-page');
    }
}

