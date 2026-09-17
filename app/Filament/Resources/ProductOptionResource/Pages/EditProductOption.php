<?php

namespace App\Filament\Resources\ProductOptionResource\Pages;

use App\Filament\Resources\ProductOptionResource;
use App\Filament\Resources\ProductOptionResource\RelationManagers\ValuesRelationManager;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages\EditProductOption as LunarEditProductOption;

/**
 * Lunar'ın varsayılanı yalnızca paylaşımlı (shared) seçeneklerde değer
 * sekmesini gösteriyordu. Demo veride seçenekler ürüne özel olduğu için
 * değerlerin her durumda düzenlenebilmesi sağlanır.
 */
class EditProductOption extends LunarEditProductOption
{
    protected static string $resource = ProductOptionResource::class;

    public function getRelationManagers(): array
    {
        return [
            ValuesRelationManager::class,
        ];
    }
}