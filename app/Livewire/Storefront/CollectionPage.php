<?php

namespace App\Livewire\Storefront;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Collection;
use Lunar\Models\Product;

#[Layout('layouts.storefront')]
class CollectionPage extends Component
{
    public string $slug;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $collection = Collection::with([
            'media',
            'urls',
            'products.media',
            'products.variants.prices.currency',
            'products.urls',
        ])
            ->whereHas('urls', fn ($query) => $query->where('slug', $slug))
            ->first();

        if (! $collection) {
            throw (new ModelNotFoundException)->setModel(Collection::class, [$slug]);
        }

        $image = $collection->media->first();

        $this->collection = [
            'name' => (string) ($collection->translateAttribute('name') ?? ''),
            'description' => (string) ($collection->translateAttribute('description') ?? ''),
            'image_url' => $image?->getUrl(),
        ];

        $this->loadProducts($collection);
    }

    protected function loadProducts(?Collection $collection = null): void
    {
        if (! $collection) {
            $collection = Collection::whereHas('urls', fn ($query) => $query->where('slug', $this->slug))->first();
        }

        $query = $collection->products()
            ->where('status', 'published');

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('attribute_data->name->en', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('attribute_data->name->tr', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $this->products = $query->with(['media', 'variants.prices', 'urls'])->get()
            ->map(function (Product $product): array {
                $variant = $product->variants->first();
                $price = $variant?->prices->first();
                $image = $product->media->first();
                $url = $product->urls->first();

                return [
                    'name' => (string) ($product->translateAttribute('name') ?? ''),
                    'description' => (string) ($product->translateAttribute('description') ?? ''),
                    'url' => $url?->slug,
                    'image_url' => $image?->getUrl(),
                    'stock' => $variant?->stock,
                    'price' => $price?->price?->value,
                ];
            })
            ->values()
            ->all();
    }

    public function updatedSearchTerm(): void
    {
        $this->loadProducts();
    }


    public function render()
    {
        return view('livewire.storefront.collection-page');
    }
}
