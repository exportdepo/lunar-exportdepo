<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductOptionResource\Pages\CreateProductOption;
use App\Filament\Resources\ProductOptionResource\Pages\EditProductOption;
use App\Filament\Resources\ProductOptionResource\Pages\ListProductOptions;
use App\Filament\Resources\ProductOptionResource\RelationManagers\ValuesRelationManager;
use Lunar\Admin\Filament\Resources\ProductOptionResource as LunarProductOptionResource;

/**
 * Ürün seçenekleri (Renk, Beden, Model ...) kaynağı.
 *
 * Lunar'ın varsayılan kaynağını genişletir:
 *  - değer tablosuna renk kodu (swatch) kolonu ve varyant sayısı ekler,
 *  - ürüne özel (shared olmayan) seçeneklerin değerleri de düzenlenebilir.
 */
class ProductOptionResource extends LunarProductOptionResource
{
    public static function getRelations(): array
    {
        return [
            ValuesRelationManager::class,
        ];
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListProductOptions::route('/'),
            'create' => CreateProductOption::route('/create'),
            'edit' => EditProductOption::route('/{record}/edit'),
        ];
    }
}