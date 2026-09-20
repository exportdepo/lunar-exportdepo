<?php

namespace App\Support;

/**
 * Vitrin (storefront) arama yardımcıları.
 *
 * Mimari not: Lunar ürün adları/açıklamaları `attribute_data` içinde JSON
 * olarak saklanır; SQLite'ta Türkçe karakterler \uXXXX kaçışıyla tutulduğu için
 * SQL LIKE güvenilir eşleşme sağlamaz. Bu yüzden arama PHP tarafında,
 * Türkçe-duyarlı normalizasyonla yapılır.
 */
class StorefrontSearch
{
    /**
     * Türkçe-duyarlı küçük harf normalizasyonu.
     *
     * Türkçe diakritikler ASCII karşılıklarına katlanır (ş->s, ı/i->i, ğ->g,
     * ç->c, ö->o, ü->u). Böylece hem 'KULAKLIK' hem 'kulaklik' hem 'kulaklık'
     * girdileri 'Bluetooth Kulaklık' ile eşleşir; klavyede Türkçe karakter
     * yazamayan kullanıcılar da sonuç alır.
     */
    public static function normalize(?string $value): string
    {
        $value = str_replace(
            ['İ', 'I', 'ı', 'Ş', 'ş', 'Ğ', 'ğ', 'Ç', 'ç', 'Ö', 'ö', 'Ü', 'ü'],
            ['i', 'i', 'i', 's', 's', 'g', 'g', 'c', 'c', 'o', 'o', 'u', 'u'],
            (string) $value
        );

        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Aranan terimin tüm kelimeleri `$haystack` içinde geçiyor mu?
     *
     * Boş terim daima eşleşir (filtre uygulanmaz). Çok kelimeli aramalarda
     * kelimelerin tamamının bulunması gerekir ('bluetooth kulaklık' gibi).
     */
    public static function matches(?string $haystack, ?string $needle): bool
    {
        $terms = preg_split('/\s+/u', trim(self::normalize($needle)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($terms === []) {
            return true;
        }

        $normalizedHaystack = self::normalize($haystack);

        foreach ($terms as $term) {
            if (! str_contains($normalizedHaystack, $term)) {
                return false;
            }
        }

        return true;
    }
}
