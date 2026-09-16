<?php

namespace App\Support;

use App\Models\WishlistItem;
use Illuminate\Support\Facades\Auth;
use Lunar\Models\Product;

/**
 * İstek listesi (wishlist) mantığını tek merkezde toplar.
 *
 * Giriş yapmamış ziyaretçiler için liste oturumda (session) tutulur;
 * giriş yapan kullanıcılar için veritabanında saklanır. Kullanıcı giriş
 * yaptığında oturumdaki liste veritabanına taşınır.
 */
class WishlistManager
{
    public const SESSION_KEY = 'wishlist';

    /**
     * Aktif istek listesindeki ürün ID'lerini (en yeni önce) döndürür.
     *
     * @return array<int, int>
     */
    public static function ids(): array
    {
        $userId = Auth::id();

        if ($userId) {
            return WishlistItem::query()
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->all();
        }

        return collect(session(self::SESSION_KEY, []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public static function count(): int
    {
        return count(self::ids());
    }

    public static function has(?int $productId): bool
    {
        if (! $productId) {
            return false;
        }

        return in_array((int) $productId, self::ids(), true);
    }

    /**
     * Ürünü listeye ekler ya da listeden çıkarır.
     *
     * @return bool İşlem sonrası yeni durum (true = listede)
     */
    public static function toggle(int $productId): bool
    {
        if (self::has($productId)) {
            self::remove($productId);

            return false;
        }

        self::add($productId);

        return true;
    }

    public static function add(int $productId): void
    {
        $userId = Auth::id();

        if ($userId) {
            // unique(user_id, product_id) sayesinde kopya kayıt oluşmaz.
            WishlistItem::firstOrCreate([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);

            return;
        }

        $ids = self::ids();

        if (! in_array($productId, $ids, true)) {
            $ids[] = $productId;
        }

        session([self::SESSION_KEY => array_values(array_unique($ids))]);
    }

    public static function remove(int $productId): void
    {
        $userId = Auth::id();

        if ($userId) {
            WishlistItem::query()
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();

            return;
        }

        $ids = array_values(array_filter(
            self::ids(),
            fn (int $id) => $id !== $productId
        ));

        session([self::SESSION_KEY => $ids]);
    }

    /**
     * İstek listesindeki ürünleri vitrin kartı dizisine dönüştürür.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function products(): array
    {
        $ids = self::ids();

        if (empty($ids)) {
            return [];
        }

        $products = Product::with([
            'media',
            'variants.prices.currency',
            'urls',
        ])
            ->where('status', 'published')
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(function (int $id) use ($products): ?array {
                $product = $products->get($id);

                if (! $product) {
                    return null;
                }

                $variant = $product->variants->first();
                $image = $product->media->first();
                $url = $product->urls->first();

                return [
                    'id' => $product->id,
                    'name' => (string) ($product->translateAttribute('name') ?? ''),
                    'description' => (string) ($product->translateAttribute('description') ?? ''),
                    'url' => $url?->slug,
                    'image_url' => $image?->getUrl(),
                    'stock' => $variant?->stock,
                    'price' => $variant ? StorefrontPricing::forVariantInclTax($variant) : null,
                    'variant_id' => $variant?->id,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Oturumdaki istek listesini kullanıcının veritabanı listesine taşır.
     * Giriş anında (web guard) çağrılır.
     */
    public static function mergeSessionIntoUser(int $userId): void
    {
        $ids = collect(session(self::SESSION_KEY, []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isNotEmpty()) {
            foreach ($ids as $id) {
                WishlistItem::firstOrCreate([
                    'user_id' => $userId,
                    'product_id' => $id,
                ]);
            }
        }

        self::clearSession();
    }

    public static function clearSession(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
