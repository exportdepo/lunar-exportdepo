<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Lunar\Models\ProductVariant;

class VariantOptions
{
    /**
     * Varyantın seçili seçenek değerlerini "Renk: Siyah · Beden: M"
     * biçiminde görüntüleme metnine çevirir.
     *
     * Seçenek değeri yoksa boş string döner; sepette/sipariş satırında
     * yalnızca ürün adı gösterilir (eski davranış korunur).
     */
    public static function label(?ProductVariant $variant, ?string $locale = null): string
    {
        if (! $variant) {
            return '';
        }

        $locale ??= app()->getLocale();

        $parts = [];

        foreach ($variant->values->sortBy('position') as $value) {
            $optionName = self::translatable($value->option?->name, $locale);
            $valueName = self::translatable($value->name, $locale);

            if ($valueName === '') {
                continue;
            }

            $parts[] = $optionName !== ''
                ? $optionName.': '.$valueName
                : $valueName;
        }

        return implode(' · ', $parts);
    }

    /**
     * Lunar çevrilebilir alanları yerel anahtarlı dizi olarak döndürür;
     * istenen yerel ayarı, yoksa ilk mevcut değeri verir.
     */
    protected static function translatable(mixed $raw, string $locale): string
    {
        if (is_array($raw) || $raw instanceof \ArrayAccess) {
            $items = $raw instanceof \ArrayObject ? $raw->getArrayCopy() : (array) $raw;
            $value = Arr::get($items, $locale) ?? Arr::first($items) ?? '';
        } else {
            $value = $raw;
        }

        return trim((string) ($value ?? ''));
    }
}
