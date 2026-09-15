<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CollectionGroupResource\Pages\EditCollectionGroup;
use App\Filament\Resources\CollectionGroupResource\Pages\ListCollectionGroups;
use App\Services\DeleteCollectionGroups;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Admin\Filament\Resources\CollectionGroupResource as LunarCollectionGroupResource;

class CollectionGroupResource extends LunarCollectionGroupResource
{
    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Seçilenleri sil')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->successNotificationTitle('Koleksiyon grupları silindi.')
                        ->action(function (EloquentCollection $records): void {
                            app(DeleteCollectionGroups::class)->handle($records);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListCollectionGroups::route('/'),
            'edit' => EditCollectionGroup::route('/{record}/edit'),
        ];
    }
}
