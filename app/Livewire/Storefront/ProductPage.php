<?php

namespace App\Livewire\Storefront;

use App\Livewire\Storefront\Concerns\InteractsWithWishlist;
use App\Models\ProductReview;
use App\Support\StorefrontPricing;
use App\Support\WishlistManager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Lunar\Facades\CartSession;
use Lunar\Models\Channel;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;
use Lunar\Models\ProductVariant;

#[Layout('layouts.storefront')]
class ProductPage extends Component
{
    use InteractsWithWishlist;

    public array $product = [];

    /** @var array<int, array{url: ?string}> */
    public array $images = [];

    /** @var array<int, string> */
    public array $features = [];

    /** @var array<int, array<string, mixed>> */
    public array $reviews = [];

    public ?float $ratingAverage = null;

    public int $reviewsCount = 0;

    public string $activeTab = 'description';

    /** @var array<int, array{id: int, label: string}> */
    public array $variants = [];

    /**
     * Vitrinde gösterilen seçenek grupları (Renk, Beden, Model ...).
     *
     * @var array<int, array{id: int, name: string, is_colour: bool, values: array<int, array{id: int, name: string, hex: ?string}>}>
     */
    public array $optionGroups = [];

    /**
     * Seçili seçenek değerleri: optionId => valueId (anahtarlar string'dir,
     * Livewire hydration'ında int anahtarlar string'e dönüşür).
     *
     * @var array<string, int>
     */
    public array $selectedOptionValues = [];

    /**
     * Kombinasyon matrisi: her varyantın sahip olduğu seçenek değerleri.
     * Stokta olmayan/var olmayan kombinasyonların elenmesi için kullanılır.
     *
     * @var array<int, array{id: int, stock: ?int, values: array<string, int>}>
     */
    public array $variantMatrix = [];

    public ?int $selectedVariantId = null;

    public string $slug = '';

    public int $quantity = 1;

    public bool $added = false;

    // Yorum formu
    public int $reviewRating = 5;

    public string $reviewTitle = '';

    public string $reviewBody = '';

    public string $reviewerName = '';

    public bool $reviewSubmitted = false;

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

        $media = $product->media()->orderBy('order_column')->get();

        $this->product = [
            'id' => $product->id,
            'name' => (string) ($product->translateAttribute('name') ?? ''),
            'description' => (string) ($product->translateAttribute('description') ?? ''),
            'image_url' => $media->first()?->getUrl(),
            'price' => null,
            'stock' => null,
            'variant_id' => null,
            'collection' => $product->collections->first()?->translateAttribute('name'),
            'collection_url' => $product->collections->first()?->urls->first()?->slug,
        ];

        $this->images = $media
            ->map(fn ($item) => ['url' => $item->getUrl()])
            ->values()
            ->all();

        $this->features = collect(
            preg_split('/\r\n|\r|\n/', (string) ($product->translateAttribute('features') ?? ''))
        )
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        $this->loadReviews($product->id);

        $this->reviewerName = (string) (auth()->user()?->name ?? '');

        $this->variants = $this->getProductVariants($product);

        // Varyasyonlu ürünlerde (Renk/Beden/Model) seçenek grupları ve kombinasyon
        // matrisi hazırlanır; varsayılan seçim stokta olan ilk kombinasyondur.
        $this->optionGroups = $this->buildOptionGroups($product);
        $this->variantMatrix = $this->buildVariantMatrix($product);

        $defaultEntry = $this->firstAvailableMatrixEntry();

        if (! empty($this->optionGroups) && $defaultEntry) {
            $this->selectedOptionValues = [];
            $this->applyMatrixEntryToSelection($defaultEntry);
            $this->selectedVariantId = $defaultEntry['id'];
            $this->updateSelectedVariant(ProductVariant::find($defaultEntry['id']));
        } else {
            $this->selectedVariantId = $product->variants->first()?->id;
            $this->updateSelectedVariant($product->variants->first());
        }

        $this->wishlistIds = WishlistManager::ids();
    }

    /**
     * Vitrin sekmeleri arasında geçiş.
     */
    public function selectTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['description', 'reviews', 'returns'], true)
            ? $tab
            : 'description';
    }

    /**
     * Onaylı yorumları yükler ve ortalama puanı hesaplar.
     */
    protected function loadReviews(int $productId): void
    {
        $reviews = ProductReview::query()
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $this->reviews = $reviews
            ->map(fn (ProductReview $review): array => [
                'name' => (string) $review->name,
                'rating' => (int) $review->rating,
                'title' => (string) $review->title,
                'body' => (string) $review->body,
                'date' => $review->created_at?->format('d.m.Y'),
            ])
            ->values()
            ->all();

        $this->reviewsCount = $reviews->count();
        $this->ratingAverage = $reviews->count() > 0
            ? round((float) $reviews->avg('rating'), 1)
            : null;
    }

    /**
     * Ziyaretçinin yorumunu onaya düşürür.
     */
    public function submitReview(): void
    {
        $this->validate([
            'reviewRating' => ['required', 'integer', 'min:1', 'max:5'],
            'reviewTitle' => ['nullable', 'string', 'max:150'],
            'reviewBody' => ['required', 'string', 'min:10', 'max:2000'],
            'reviewerName' => ['required', 'string', 'max:100'],
        ]);

        ProductReview::create([
            'product_id' => $this->product['id'],
            'user_id' => auth()->id(),
            'name' => $this->reviewerName,
            'rating' => $this->reviewRating,
            'title' => $this->reviewTitle !== '' ? $this->reviewTitle : null,
            'body' => $this->reviewBody,
            'is_approved' => false,
        ]);

        $this->reset('reviewTitle', 'reviewBody');
        $this->reviewRating = 5;
        $this->reviewSubmitted = true;
    }

    protected function getProductVariants(Product $product): array
    {
        return $product->variants->map(fn (ProductVariant $variant): array => [
            'id' => $variant->id,
            'label' => $variant->getOption() ?: $variant->sku ?: 'Varyant '.$variant->id,
        ])->values()->all();
    }

    /**
     * Ürünün seçeneklerini vitrin gösterimine uygun gruplara dönüştürür.
     *
     * @return array<int, array{id: int, name: string, is_colour: bool, values: array<int, array{id: int, name: string, hex: ?string}>}>
     */
    protected function buildOptionGroups(Product $product): array
    {
        return $product->productOptions()
            ->with('values')
            ->get()
            ->map(function (ProductOption $option): array {
                $handle = (string) $option->handle;

                return [
                    'id' => $option->id,
                    'name' => (string) ($option->translate('name') ?: Str::headline($handle)),
                    'is_colour' => Str::contains($handle, ['renk', 'color', 'colour']),
                    'values' => $option->values
                        ->map(fn (ProductOptionValue $value): array => [
                            'id' => $value->id,
                            'name' => (string) $value->translate('name'),
                            'hex' => $value->meta['hex'] ?? null,
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Her varyantın hangi seçenek değerlerine sahip olduğunu çıkarır.
     *
     * @return array<int, array{id: int, stock: ?int, values: array<string, int>}>
     */
    protected function buildVariantMatrix(Product $product): array
    {
        return $product->variants
            ->map(function (ProductVariant $variant): array {
                $values = [];

                foreach ($variant->values as $value) {
                    $values[(string) $value->product_option_id] = $value->id;
                }

                return [
                    'id' => $variant->id,
                    'stock' => $variant->stock,
                    'values' => $values,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Seçenek değeri çiftlerinden sıradan bağımsız karşılaştırma anahtarı üretir.
     *
     * @param  array<string|int, int>  $pairs
     */
    protected function matrixKey(array $pairs): string
    {
        $normalised = [];

        foreach ($pairs as $optionId => $valueId) {
            $normalised[(string) $optionId] = (int) $valueId;
        }

        ksort($normalised);

        $parts = [];

        foreach ($normalised as $optionId => $valueId) {
            $parts[] = $optionId.':'.$valueId;
        }

        return implode('|', $parts);
    }

    /**
     * Seçilen kombinasyona karşılık gelen varyant kaydını bulur.
     *
     * @return array{id: int, stock: ?int, values: array<string, int>}|null
     */
    protected function matrixEntryForSelection(): ?array
    {
        if (empty($this->selectedOptionValues)) {
            return null;
        }

        $key = $this->matrixKey($this->selectedOptionValues);

        foreach ($this->variantMatrix as $entry) {
            if ($this->matrixKey($entry['values']) === $key) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Stokta olan ilk varyant kaydını (hepsi stok dışıysa ilk kaydı) döndürür.
     *
     * @return array{id: int, stock: ?int, values: array<string, int>}|null
     */
    protected function firstAvailableMatrixEntry(): ?array
    {
        foreach ($this->variantMatrix as $entry) {
            if ($entry['stock'] === null || $entry['stock'] > 0) {
                return $entry;
            }
        }

        return $this->variantMatrix[0] ?? null;
    }

    /**
     * Verilen seçenek değerini içeren ilk stokta varyantı bulur.
     *
     * @return array{id: int, stock: ?int, values: array<string, int>}|null
     */
    protected function firstMatrixEntryWithValue(int $optionId, int $valueId): ?array
    {
        foreach ($this->variantMatrix as $entry) {
            if (! $this->entryHasValue($entry, $optionId, $valueId)) {
                continue;
            }

            if ($entry['stock'] !== null && $entry['stock'] <= 0) {
                continue;
            }

            return $entry;
        }

        foreach ($this->variantMatrix as $entry) {
            if ($this->entryHasValue($entry, $optionId, $valueId)) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * Varyant kaydındaki seçenek değerlerini mevcut seçime yazar.
     *
     * @param  array{id: int, stock: ?int, values: array<string, int>}  $entry
     */
    protected function applyMatrixEntryToSelection(array $entry): void
    {
        foreach ($entry['values'] as $optionId => $valueId) {
            $this->selectedOptionValues[(string) $optionId] = (int) $valueId;
        }
    }

    /**
     * @param  array{id: int, stock: ?int, values: array<string, int>}  $entry
     */
    protected function entryHasValue(array $entry, int $optionId, int $valueId): bool
    {
        return (int) ($entry['values'][(string) $optionId] ?? 0) === $valueId;
    }

    /**
     * Varyantın verilen tüm seçenek değerlerini içerip içermediğini kontrol eder.
     *
     * @param  array{id: int, stock: ?int, values: array<string, int>}  $entry
     * @param  array<string|int, int>  $expected
     */
    protected function entryMatchesSelection(array $entry, array $expected): bool
    {
        foreach ($expected as $optionId => $valueId) {
            if (! $this->entryHasValue($entry, (int) $optionId, (int) $valueId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Vitrinden bir seçenek değeri seçildiğinde çağrılır.
     *
     * Seçilen kombinasyon mevcut değilse (örn. o renkte o beden üretilmemiş),
     * diğer seçenekler bu değeri içeren ilk stokta varyanta göre hizalanır;
     * böylece kullanıcı hiçbir zaman geçersiz kombinasyonda kalmaz.
     */
    public function selectOptionValue(int $optionId, int $valueId): void
    {
        $this->selectedOptionValues[(string) $optionId] = $valueId;
        $this->added = false;

        if (! $this->matrixEntryForSelection()) {
            $entry = $this->firstMatrixEntryWithValue($optionId, $valueId);

            if ($entry) {
                $this->applyMatrixEntryToSelection($entry);
            }
        }

        $this->syncSelectedVariant();
    }

    /**
     * Mevcut seçimi varyanta bağlar; kombinasyon yoksa ürünü satın alınamaz yapar.
     */
    protected function syncSelectedVariant(): void
    {
        $entry = $this->matrixEntryForSelection();

        if (! $entry) {
            $this->selectedVariantId = null;
            $this->product['variant_id'] = null;
            $this->product['price'] = null;
            $this->product['stock'] = 0;

            return;
        }

        $this->selectedVariantId = $entry['id'];
        $this->updateSelectedVariant(ProductVariant::find($entry['id']));
    }

    /**
     * Seçenek değerinin seçili olup olmadığı (vitrin görünümü için).
     */
    public function isValueSelected(int $optionId, int $valueId): bool
    {
        return (int) ($this->selectedOptionValues[(string) $optionId] ?? 0) === $valueId;
    }

    /**
     * Seçenek değerinin, diğer mevcut seçimlerle birlikte stokta olup olmadığı.
     *
     * Vitrinde stokta olmayan kombinasyonlar soluk/çizili gösterilir ve
     * tıklanamaz (disabled).
     */
    public function isValueAvailable(int $optionId, int $valueId): bool
    {
        $otherSelections = [];

        foreach ($this->selectedOptionValues as $selectedOptionId => $selectedValueId) {
            if ((int) $selectedOptionId === $optionId) {
                continue;
            }

            $otherSelections[(string) $selectedOptionId] = (int) $selectedValueId;
        }

        foreach ($this->variantMatrix as $entry) {
            if (! $this->entryHasValue($entry, $optionId, $valueId)) {
                continue;
            }

            if ($entry['stock'] !== null && $entry['stock'] <= 0) {
                continue;
            }

            if ($this->entryMatchesSelection($entry, $otherSelections)) {
                return true;
            }
        }

        return false;
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

        // Seçenekli üründe varyant doğrudan değiştirilirse (programatik), seçenek
        // butonlarının vurgusu da bu varyantla eşitlenir.
        if (empty($this->optionGroups)) {
            return;
        }

        foreach ($this->variantMatrix as $entry) {
            if ($entry['id'] !== $variantId) {
                continue;
            }

            $this->selectedOptionValues = [];
            $this->applyMatrixEntryToSelection($entry);

            break;
        }
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

        $this->product['price'] = StorefrontPricing::forVariantInclTax($variant, $this->quantity);
        $this->product['stock'] = $variant->stock;
        $this->product['variant_id'] = $variant->id;
    }

    public function render()
    {
        return view('livewire.storefront.product-page');
    }
}
