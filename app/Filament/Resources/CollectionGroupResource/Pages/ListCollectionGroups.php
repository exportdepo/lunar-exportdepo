<?php

namespace App\Filament\Resources\CollectionGroupResource\Pages;

use App\Filament\Resources\CollectionGroupResource;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListCollectionGroups extends BaseListRecords
{
    protected static string $resource = CollectionGroupResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
