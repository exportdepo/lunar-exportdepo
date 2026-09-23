<?php

namespace App\Livewire\Storefront;

use App\Livewire\Storefront\Concerns\InteractsWithCart;
use App\Livewire\Storefront\Concerns\InteractsWithWishlist;
use App\Support\StorefrontPricing;
use App\Support\StorefrontRatings;
use App\Support\StorefrontSearch;
use App\Support\WishlistManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Lunar\Models\Attribute;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Models\Tag;

#[Layout('layouts.storefront')]
class CollectionPage extends Component
{
    use InteractsWithCart;
    use InteractsWithWishlist;

    public string $slug;

    public array $collection = [];

    public array $products = [];

    #[Url(as: 'q', except: '')]
    public string $searchTerm = '';

    #[Url(as: 'category', except: null)]
    public ?int $selectedCategoryId = null;

    /**
     * Çoklu marka filtresi (OR): [brand_key => true, ...]; anahtar marka adının slug'ı.
     */
    #[Url(as: 'brand', except: [])]
    public array $brandFilters = [];

    /**
     * Çoklu varyant seçeneği filtresi: [group_key => [value_key => true, ...]].
     * Aynı grupta seçilenler OR, gruplar arası AND uygulanır.
     * Örn. Renk=(siyah OR beyaz) AND Beden=m.
     */
    #[Url(as: 'options', except: [])]
    public array $optionFilters = [];

    /**
     * Ürün seviyesinde filterable öznitelik filtresi: handle => değer.
     */
    #[Url(as: 'attrs', except: [])]
    public array $attributeFilters = [];

    /**
     * Çoklu etiket filtresi (OR): [tag_key => true, ...]; anahtar etiket değerinin slug'ı.
     */
    #[Url(as: 'tags', except: [])]
    public array $tagFilters = [];

    public array $categories = [];

    public array $brands = [];

    /** Etiket filtre seçenekleri (key, id, value). */
    public array $tags = [];

    public array $options = [];

    /** Filterable öznitelik seçenekleri (handle, ad, values). */
    public array $attributeFilterOptions = [];

    #[Url(as: 'sort', except: 'default')]
    public string $sortBy = 'default';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $collection = Collection::with([
            'media',
            'urls',
            'products.media',
            'products.variants.prices.currency',
            'products.urls',
            'products.variants.values.option',
            'products.tags',
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
        $this->loadBrands($collection);
        $this->loadTags($collection);
        $this->loadOptions($collection);
        $this->loadAttributeFilterOptions($collection);
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
            ->reject(fn ($cat) => $cat->id === $collection->id)
            ->values()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => (string) ($cat->translateAttribute('name') ?? ''),
            ])
            ->all();
    }

    /**
     * Koleksiyon ürünlerindeki Lunar markalarını (lunar_brands → brand_id)
     * toplar; çoklu seçim filtresi için her markaya slug anahtar üretir.
     */
    protected function loadBrands(Collection $collection): void
    {
        $brands = $collection->products()
            ->where('status', 'published')
            ->with('brand')
            ->get()
            ->map(fn ($product) => $product->brand)
            ->filter()
            ->unique('id')
            ->values();

        $list = [];
        $usedKeys = [];
        foreach ($brands as $brand) {
            $base = Str::slug($brand->name ?? '') ?: 'marka';
            $key = $base;
            $counter = 2;
            while (isset($usedKeys[$key])) {
                $key = $base.'-'.$counter++;
            }
            $usedKeys[$key] = true;

            $list[] = [
                'key' => $key,
                'id' => (int) $brand->id,
                'name' => (string) ($brand->name ?? ''),
            ];
        }

        $this->brands = $list;
    }

    /**
     * Koleksiyon ürünlerindeki etiketleri (lunar_tags, morph taggables) toplar;
     * çoklu seçim filtresi için değer slug'ından anahtar üretir, alfabetik sıralar.
     */
    protected function loadTags(Collection $collection): void
    {
        $tags = $collection->products()
            ->where('status', 'published')
            ->with('tags')
            ->get()
            ->flatMap(fn ($product) => $product->tags)
            ->unique('id')
            ->values()
            ->sortBy('value');

        $list = [];
        $usedKeys = [];
        foreach ($tags as $tag) {
            $base = Str::slug($tag->value ?? '') ?: 'etiket';
            $key = $base;
            $counter = 2;
            while (isset($usedKeys[$key])) {
                $key = $base.'-'.$counter++;
            }
            $usedKeys[$key] = true;

            $list[] = [
                'key' => $key,
                'id' => (int) $tag->id,
                'value' => (string) ($tag->value ?? ''),
            ];
        }

        $this->tags = $list;
    }

    /**
     * Varyant seçeneklerini (Renk, Beden, Model …) Lunar ProductOption yapısından
     * toplar ve aynı ada sahip option'ları tek grupta birleştirir. Seeder/panel
     * ürün başına ayrı "Renk"/"Beden" tanımı yarattığından gruplama isim bazlıdır;
     * değerler etikete göre deduplanır ve aynı etiketli tüm value ID'leri taşınır.
     */
    protected function loadOptions(Collection $collection): void
    {
        $optionIdsByName = [];
        $valueIdsByLabel = [];
        $positionByLabel = [];

        $variantValues = $collection->products()
            ->where('status', 'published')
            ->with('variants.values.option')
            ->get()
            ->flatMap(fn ($product) => $product->variants)
            ->flatMap(fn ($variant) => $variant->values);

        foreach ($variantValues as $value) {
            if (! $value->option) {
                continue;
            }

            $groupName = (string) ($value->option->translate('name') ?? 'Özellik');
            $label = (string) ($value->translate('name') ?? '');

            if ($label === '') {
                continue;
            }

            $optionIdsByName[$groupName][(int) $value->product_option_id] = true;
            $valueIdsByLabel[$groupName][$label][(int) $value->id] = (int) $value->id;

            $position = (int) ($value->position ?? 0);
            $positionByLabel[$groupName][$label] = min($positionByLabel[$groupName][$label] ?? $position, $position);
        }

        $groups = [];
        $usedGroupKeys = [];
        foreach ($valueIdsByLabel as $groupName => $labels) {
            $groupBase = Str::slug($groupName) ?: 'ozellik';
            $groupKey = $groupBase;
            $groupCounter = 2;
            while (isset($usedGroupKeys[$groupKey])) {
                $groupKey = $groupBase.'-'.$groupCounter++;
            }
            $usedGroupKeys[$groupKey] = true;

            $values = [];
            $usedValueKeys = [];
            foreach (collect($labels)->keys()->sortBy(fn ($label) => $positionByLabel[$groupName][$label])->all() as $label) {
                $valueBase = Str::slug($label) ?: 'deger';
                $valueKey = $valueBase;
                $valueCounter = 2;
                while (isset($usedValueKeys[$valueKey])) {
                    $valueKey = $valueBase.'-'.$valueCounter++;
                }
                $usedValueKeys[$valueKey] = true;

                $values[] = [
                    'key' => $valueKey,
                    'label' => (string) $label,
                    'value_ids' => array_values($labels[$label]),
                ];
            }

            $groups[] = [
                'key' => $groupKey,
                'name' => (string) $groupName,
                'option_ids' => array_keys($optionIdsByName[$groupName]),
                'values' => $values,
            ];
        }

        $this->options = $groups;
    }

    /**
     * Lunar `filterable` ürün özniteliklerini (attribute_type=product) ve
     * koleksiyon ürünlerindeki distinct değerleri toplar.
     *
     * Not: attribute_data Lunar FieldType JSON'u tuttuğu için SQL LIKE
     * güvenilir değildir; seçenek toplama ve filtreleme PHP'de yapılır.
     */
    protected function loadAttributeFilterOptions(Collection $collection): void
    {
        $attributes = Attribute::query()
            ->where('attribute_type', 'product')
            ->where('filterable', true)
            ->get(['id', 'handle', 'name']);

        if ($attributes->isEmpty()) {
            $this->attributeFilterOptions = [];

            return;
        }

        $valuesByHandle = [];
        foreach ($collection->products()->where('status', 'published')->get() as $product) {
            foreach ($attributes as $attribute) {
                $value = $product->translateAttribute($attribute->handle);
                if (is_string($value) && $value !== '') {
                    $valuesByHandle[$attribute->handle][$value] = $value;
                }
            }
        }

        $this->attributeFilterOptions = $attributes
            ->map(fn (Attribute $attribute): array => [
                'handle' => (string) $attribute->handle,
                'name' => (string) ($attribute->translate('name') ?? $attribute->handle),
                'values' => array_values($valuesByHandle[$attribute->handle] ?? []),
            ])
            ->filter(fn (array $option): bool => $option['values'] !== [])
            ->values()
            ->all();
    }

    protected function loadProducts(?Collection $collection = null): void
    {
        if (! $collection) {
            $collection = Collection::whereHas('urls', fn ($query) => $query->where('slug', $this->slug))->first();
        }

        // Her erişim yolunda (hook, URL hydration, clearFilters) kanonik biçimi garanti eder.
        $this->optionFilters = $this->normalizeOptionFilters($this->optionFilters);
        $this->brandFilters = $this->normalizeBrandFilters($this->brandFilters);
        $this->tagFilters = $this->normalizeTagFilters($this->tagFilters);

        $query = $collection->products()
            ->where('status', 'published')
            ->with(['media', 'variants.prices', 'variants.values', 'urls', 'tags']);

        if ($this->selectedCategoryId) {
            $query->whereHas('collections', fn ($q) => $q->where('lunar_collections.id', $this->selectedCategoryId));
        }

        // Çoklu marka filtresi: seçilen markaların ürünleri (OR); bilinmeyen
        // anahtarlar (eski ID tabanlı URL) sessizce yok sayılır.
        $brandIds = collect($this->brands)
            ->filter(fn (array $brand): bool => isset($this->brandFilters[$brand['key']]))
            ->pluck('id')
            ->values()
            ->all();
        if ($brandIds !== []) {
            $query->whereIn('brand_id', $brandIds);
        }

        // Çoklu etiket filtresi: seçilen etiketlerden herhangi birine sahip ürünler (OR);
        // bilinmeyen anahtarlar sessizce yok sayılır.
        $tagIds = collect($this->tags)
            ->filter(fn (array $tag): bool => isset($this->tagFilters[$tag['key']]))
            ->pluck('id')
            ->values()
            ->all();
        if ($tagIds !== []) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('lunar_tags.id', $tagIds));
        }

        // Çoklu seçenek filtresi: birleştirilmiş gruplar (Renk, Beden …) arası AND;
        // grup içindeki seçimler aynı etiketli tüm value ID'lerine açılıp whereIn ile OR.
        foreach ($this->optionFilters as $groupKey => $selectedValueKeys) {
            $group = collect($this->options)->firstWhere('key', (string) $groupKey);
            if (! $group) {
                continue; // Bilinmeyen anahtar (eski ID tabanlı URL) sessizce yok sayılır.
            }

            $ids = [];
            foreach (array_keys((array) $selectedValueKeys) as $valueKey) {
                $value = collect($group['values'])->firstWhere('key', (string) $valueKey);
                if ($value) {
                    $ids = array_merge($ids, $value['value_ids']);
                }
            }

            if ($ids === []) {
                continue;
            }

            $query->whereHas('variants.values', function ($q) use ($group, $ids) {
                $q->whereIn('lunar_product_option_values.product_option_id', $group['option_ids'])
                    ->whereIn('lunar_product_option_values.id', $ids);
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

        foreach ($this->attributeFilters as $handle => $value) {
            if (! filled($value)) {
                continue;
            }
            $products = $products->filter(
                fn (Product $product): bool => (string) ($product->translateAttribute((string) $handle) ?? '') === (string) $value
            );
        }

        $products = match ($this->sortBy) {
            'price_asc' => $products->sortBy(fn (Product $product): float => $product->variants->first()
                ? (float) (StorefrontPricing::forVariantInclTax($product->variants->first()) ?? PHP_FLOAT_MAX)
                : PHP_FLOAT_MAX)->values(),
            'price_desc' => $products->sortByDesc(fn (Product $product): float => $product->variants->first()
                ? (float) (StorefrontPricing::forVariantInclTax($product->variants->first()) ?? 0)
                : -1)->values(),
            'newest' => $products->sortByDesc('created_at')->values(),
            default => $products,
        };

        $ratings = StorefrontRatings::forProducts($products->modelKeys());

        $this->products = $products
            ->map(function (Product $product) use ($ratings): array {
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
                    'tags' => $product->tags->pluck('value')->all(),
                    'rating' => $ratings[$product->id]['rating'] ?? null,
                    'reviews_count' => $ratings[$product->id]['reviews_count'] ?? 0,
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

    public function updatedBrandFilters(): void
    {
        // Normalize loadProducts() başındaki hunide yapılır; burada yalnızca yeniden listeleme.
        $this->loadProducts();
    }

    public function updatedTagFilters(): void
    {
        // Normalize loadProducts() başındaki hunide yapılır; burada yalnızca yeniden listeleme.
        $this->loadProducts();
    }

    public function updatedSortBy(): void
    {
        if (! in_array($this->sortBy, ['default', 'newest', 'price_asc', 'price_desc'], true)) {
            $this->sortBy = 'default';
        }
        $this->loadProducts();
    }

    public function updatedOptionFilters(mixed $value, ?string $key = null): void
    {
        // Normalize loadProducts() başındaki hunide yapılır; burada yalnızca yeniden listeleme.
        $this->loadProducts();
    }

    public function updatedAttributeFilters(mixed $value, ?string $key = null): void
    {
        if ($key === null) {
            $this->attributeFilters = array_filter(is_array($value) ? $value : [], fn ($v): bool => filled($v));
        } elseif (! filled($this->attributeFilters[$key] ?? null)) {
            unset($this->attributeFilters[$key]);
        }
        $this->loadProducts();
    }

    /**
     * Kanonik biçim: [group_key => [value_key => true, ...]].
     * Checkbox (assoc: value_key => true/false) ve liste (URL: [key, ...])
     * formlarını destekler; falsey girişleri atar.
     */
    protected function normalizeOptionFilters(array $filters): array
    {
        $normalized = [];
        foreach ($filters as $groupKey => $values) {
            $keys = [];

            if (is_array($values)) {
                if (array_is_list($values)) {
                    foreach ($values as $valueKey) {
                        if (filled($valueKey)) {
                            $keys[(string) $valueKey] = true;
                        }
                    }
                } else {
                    foreach ($values as $valueKey => $checked) {
                        if ($checked !== false && $checked !== null && $checked !== '') {
                            $keys[(string) $valueKey] = true;
                        }
                    }
                }
            } elseif (filled($values)) {
                $keys[(string) $values] = true;
            }

            if ($keys !== []) {
                $normalized[(string) $groupKey] = $keys;
            }
        }

        return $normalized;
    }

    /**
     * Kanonik biçim: [brand_key => true, ...]; falsey girişleri atar.
     */
    protected function normalizeBrandFilters(array $filters): array
    {
        $normalized = [];
        foreach ($filters as $brandKey => $checked) {
            if ($checked !== false && $checked !== null && $checked !== '') {
                $normalized[(string) $brandKey] = true;
            }
        }

        return $normalized;
    }

    /**
     * Kanonik biçim: [tag_key => true, ...]; falsey girişleri atar.
     */
    protected function normalizeTagFilters(array $filters): array
    {
        $normalized = [];
        foreach ($filters as $tagKey => $checked) {
            if ($checked !== false && $checked !== null && $checked !== '') {
                $normalized[(string) $tagKey] = true;
            }
        }

        return $normalized;
    }

    public function clearFilters(): void
    {
        $this->searchTerm = '';
        $this->selectedCategoryId = null;
        $this->brandFilters = [];
        $this->tagFilters = [];
        $this->optionFilters = [];
        $this->attributeFilters = [];
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
