<?php

namespace App\Filament\Resources\CollectionGroupResource\Pages;

use App\Filament\Resources\CollectionGroupResource;
use Filament\Actions\CreateAction;
use Lunar\Admin\Support\Pages\BaseListRecords;

class ListCollectionGroups extends BaseListRecords
{
    protected static string $resource = CollectionGroupResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
