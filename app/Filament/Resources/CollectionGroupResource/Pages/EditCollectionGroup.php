<?php

namespace App\Filament\Resources\CollectionGroupResource\Pages;

use App\Filament\Resources\CollectionGroupResource;
use App\Services\DeleteCollectionGroups;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Admin\Filament\Resources\CollectionGroupResource\Widgets\CollectionTreeView;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditCollectionGroup extends BaseEditRecord
{
    protected static string $resource = CollectionGroupResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->action(function ($record): void {
                    app(DeleteCollectionGroups::class)->handle(new EloquentCollection([$record]));
                }),
        ];
    }

    protected function getDefaultFooterWidgets(): array
    {
        return [
            CollectionTreeView::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
