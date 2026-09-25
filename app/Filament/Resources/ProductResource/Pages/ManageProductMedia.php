<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Support\RelationManagers\MediaRelationManager;
use Filament\Resources\RelationManagers\RelationGroup;
use Lunar\Admin\Filament\Resources\ProductResource\Pages\ManageProductMedia as LunarManageProductMedia;

/**
 * Ürün medya sayfası: Lunar varsayılanı yerine, düzenleme modalında görsel
 * değiştirmeye izin veren kendi media relation manager'ımızı kullanır.
 */
class ManageProductMedia extends LunarManageProductMedia
{
    public function getRelationManagers(): array
    {
        $relationManagers = [];

        foreach ($this->getOwnerRecord()->getRegisteredMediaCollections() as $mediaCollection) {
            $relationManagers[] = MediaRelationManager::make([
                'mediaCollection' => $mediaCollection->name,
            ]);
        }

        return [
            RelationGroup::make('Media', $relationManagers),
        ];
    }
}
