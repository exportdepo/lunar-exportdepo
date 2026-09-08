<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Lunar\Models\Order;
use Lunar\Models\ProductVariant;

class OrderStockService
{
    public function decrementFor(Order $order): void
    {
        $variantMorphClass = (new ProductVariant)->getMorphClass();

        DB::transaction(function () use ($order, $variantMorphClass): void {
            $lockedOrder = Order::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (data_get($lockedOrder->meta, 'stock_decremented_at')) {
                return;
            }

            $quantities = $lockedOrder->productLines
                ->filter(fn ($line): bool => $line->purchasable_type === $variantMorphClass)
                ->groupBy('purchasable_id')
                ->map(fn ($lines): int => $lines->sum('quantity'))
                ->sortKeys();

            foreach ($quantities as $variantId => $quantity) {
                $variant = ProductVariant::query()
                    ->lockForUpdate()
                    ->find($variantId);

                if (! $variant || $variant->stock === null) {
                    continue;
                }

                $updated = ProductVariant::query()
                    ->whereKey($variant->getKey())
                    ->where('stock', '>=', $quantity)
                    ->decrement('stock', $quantity);

                if ($updated !== 1) {
                    throw new \RuntimeException('Sipariş için yeterli ürün stoğu bulunmuyor.');
                }
            }

            $lockedOrder->update([
                'meta' => array_merge((array) $lockedOrder->meta, [
                    'stock_decremented_at' => now()->toIso8601String(),
                ]),
            ]);
        });
    }
}