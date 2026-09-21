<?php

namespace App\Livewire\Storefront;

use App\Livewire\Storefront\Concerns\InteractsWithCart;
use App\Livewire\Storefront\Concerns\InteractsWithWishlist;
use App\Support\StorefrontPricing;
use App\Support\StorefrontSearch;
use App\Support\WishlistManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Models\Collection;
use Lunar\Models\Product;

#[Layout('layouts.storefront')]
class CollectionPage extends Component
{
    use InteractsWithCart;
    use InteractsWithWishlist;

    public string $slug;

    public array $collection = [];

    public array $products = [];

    public string $searchTerm = '';

    public ?int $selectedCategoryId = null;

    public ?int $selectedVariantAttributeId = null;

    public ?string $selectedVariantValue = null;

    public array $categories = [];

    public array $variantAttributes = [];

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $collection = Collection::with([
            'media',
            'urls',
            'products.media',
            'products.variants.prices.currency',
            'products.urls',
            'products.variants.values',
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

        $this->loadCategories($collection);
        $this->loadVariantAttributes($collection);
        $this->loadProducts($collection);

        $this->wishlistIds = WishlistManager::ids();
    }

    protected function loadCategories(Collection $collection): void
    {
        $this->categories = $collection->products()
            ->where('status', 'published')
            ->with('collections')
            ->get()
            ->flatMap(fn ($product) => $product->collections)
            ->unique('id')
            ->values()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => (string) ($cat->translateAttribute('name') ?? ''),
            ])
            ->all();
    }

    protected function loadVariantAttributes(Collection $collection): void
    {
        $attributes = $collection->products()
            ->where('status', 'published')
            ->with('variants.values')
            ->get()
            ->flatMap(fn ($product) => $product->variants)
            ->flatMap(fn ($variant) => $variant->values)
            ->groupBy('attribute_id')
            ->map(function ($values) {
                $first = $values->first();

                return [
                    'attribute_id' => $first->attribute_id,
                    'attribute_name' => $first->attribute->name ?? 'Özellik',
                    'values' => $values->unique('value')->values()->map(fn ($v) => [
                        'value' => $v->value,
                        'label' => $v->value,
                    ])->all(),
                ];
            })
            ->values()
            ->all();

        $this->variantAttributes = $attributes;
    }

    protected function loadProducts(?Collection $collection = null): void
    {
        if (! $collection) {
            $collection = Collection::whereHas('urls', fn ($query) => $query->where('slug', $this->slug))->first();
        }

        $query = $collection->products()
            ->where('status', 'published')
            ->with(['media', 'variants.prices', 'variants.values', 'urls']);

        if ($this->selectedCategoryId) {
            $query->whereHas('collections', fn ($q) => $q->where('lunar_collections.id', $this->selectedCategoryId));
        }

        if ($this->selectedVariantAttributeId && $this->selectedVariantValue) {
            $query->whereHas('variants.values', function ($q) {
                $q->where('attribute_id', $this->selectedVariantAttributeId)
                    ->where('value', $this->selectedVariantValue);
            });
        }

        $products = $query->get();

        // Not: attribute_data Lunar FieldType'ları JSON olarak saklar; SQLite'ta
        // Türkçe karakterler \uXXXX kaçışıyla tutulduğu için SQL LIKE güvenilir
        // eşleşme sağlamaz. Bu yüzden arama PHP tarafında yapılır.
        if ($this->searchTerm !== '') {
            $products = $products->filter(fn (Product $product): bool => StorefrontSearch::matches(
                (string) ($product->translateAttribute('name') ?? '').' '.
                (string) ($product->translateAttribute('description') ?? ''),
                $this->searchTerm
            ));
        }
        $this->products = $products
            ->map(function (Product $product): array {
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
            })
            ->values()
            ->all();
    }

    public function updatedSearchTerm(): void
    {
        $this->loadProducts();
    }

    public function updatedSelectedCategoryId(): void
    {
        $this->loadProducts();
    }

    public function updatedSelectedVariantAttributeId(): void
    {
        $this->selectedVariantValue = null;
        $this->loadProducts();
    }

    public function updatedSelectedVariantValue(): void
    {
        $this->loadProducts();
    }

    public function clearFilters(): void
    {
        $this->searchTerm = '';
        $this->selectedCategoryId = null;
        $this->selectedVariantAttributeId = null;
        $this->selectedVariantValue = null;
        $this->loadProducts();
    }

    public function render()
    {
        return view('livewire.storefront.collection-page')->layoutData([
            'title' => (string) ($this->collection['name'] ?? 'Koleksiyon'),
            'description' => Str::limit((string) ($this->collection['description'] ?? ''), 150),
        ]);
    }
}
