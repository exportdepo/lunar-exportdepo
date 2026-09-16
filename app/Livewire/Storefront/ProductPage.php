<?php

namespace App\Livewire\Storefront;

use App\Livewire\Storefront\Concerns\InteractsWithWishlist;
use App\Models\ProductReview;
use App\Support\StorefrontPricing;
use App\Support\WishlistManager;
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

    public array $variants = [];

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

        $this->selectedVariantId = $product->variants->first()?->id;
        $this->updateSelectedVariant($product->variants->first());

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

        $this->product['price'] = StorefrontPricing::forVariantInclTax($variant, $this->quantity);
        $this->product['stock'] = $variant->stock;
        $this->product['variant_id'] = $variant->id;
    }

    public function render()
    {
        return view('livewire.storefront.product-page');
    }
}
