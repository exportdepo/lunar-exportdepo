<?php

namespace App\Support;

use App\Models\ProductReview;
use Illuminate\Support\Collection;

/**
 * Ürün kartları için toplu değerlendirme özeti.
 *
 * Vitrin/koleksiyon/istek listesi ızgaralarında N+1 sorgu oluşmasını
 * önlemek için tüm ürün kimliklerini tek sorguda gruplar ve yalnızca
 * onaylı yorumlardan (ProductPage ile tutarlı) ortalama puan + adet döndürür.
 */
class StorefrontRatings
{
    /**
     * @param  array<int, int>  $productIds
     * @return array<int, array{rating: float, reviews_count: int}>
     */
    public static function forProducts(array $productIds): array
    {
        $productIds = collect($productIds)
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($productIds === []) {
            return [];
        }

        /** @var Collection<int, object{product_id:int, rating_avg:string|float, rating_count:int}> $rows */
        $rows = ProductReview::query()
            ->selectRaw('product_id, AVG(rating) as rating_avg, COUNT(*) as rating_count')
            ->whereIn('product_id', $productIds)
            ->where('is_approved', true)
            ->groupBy('product_id')
            ->get();

        $summary = [];

        foreach ($rows as $row) {
            $summary[(int) $row->product_id] = [
                'rating' => round((float) $row->rating_avg, 1),
                'reviews_count' => (int) $row->rating_count,
            ];
        }

        return $summary;
    }
}
