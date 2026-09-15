<?php

namespace App\Support;

use Lunar\Exceptions\MissingCurrencyPriceException;
use Lunar\Facades\Pricing;
use Lunar\Models\ProductVariant;

class StorefrontPricing
{
    /**
     * Vitrinde gosterilen birim fiyat.
     *
     * Sepetle birebir ayni cozumlemeyi kullanir (Lunar PricingManager):
     * miktar kademeleri, musteri grubu fiyatlari ve lunar.pricing.pipelines
     * her iki tarafta da ayni sekilde uygulanir.
     */
    public static function forVariant(ProductVariant $variant, int $quantity = 1): ?int
    {
        return self::resolve($variant, $quantity, inclTax: false);
    }

    /**
     * Vitrinde gosterilen KDV dahil birim fiyat.
     *
     * Varsayilan vergi bolgesi (Turkiye) uzerinden matched->priceIncTax() degeri
     * dondurur; sepetin unitPriceInclTax hesabiyla birebir ayni kaynagi kullanir.
     */
    public static function forVariantInclTax(ProductVariant $variant, int $quantity = 1): ?int
    {
        return self::resolve($variant, $quantity, inclTax: true);
    }

    protected static function resolve(ProductVariant $variant, int $quantity, bool $inclTax): ?int
    {
        try {
            $response = Pricing::qty(max(1, $quantity))->for($variant)->get();

            if (! $matched = $response->matched) {
                return null;
            }

            if ($inclTax) {
                $taxZone = \Lunar\Models\TaxZone::where('default', true)->first();

                return $taxZone ? $matched->priceIncTax($taxZone)->value : null;
            }

            return $matched->price?->value;
        } catch (MissingCurrencyPriceException $e) {
            // Beklenen durum: urunun aktif (varsayilan) para biriminde fiyati yok.
            // Gorunum "-" gosterir; sorunun fark edilmesi icin log'a duser.
            logger()->warning('StorefrontPricing: variant :id has no price in the active (default) currency.', ['id' => $variant->id]);

            return null;
        } catch (\Throwable $e) {
            // Beklenmedik hata (orn. yanlis varsayilan para birimi, bozuk fiyat verisi).
            // Sessizce yutup "-" gostermek yerine izlenebilir sekilde raporlanir.
            report($e);

            return null;
        }
    }
}