<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaxClassResource\Pages\CreateTaxClass;
use App\Filament\Resources\TaxClassResource\Pages\EditTaxClass;
use App\Filament\Resources\TaxClassResource\Pages\ListTaxClasses;
use App\Services\DeleteTaxClasses;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Admin\Filament\Resources\TaxClassResource as LunarTaxClassResource;

class TaxClassResource extends LunarTaxClassResource
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
                        ->successNotificationTitle('Vergi sınıfları silindi.')
                        ->action(function (EloquentCollection $records): void {
                            app(DeleteTaxClasses::class)->handle($records);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListTaxClasses::route('/'),
            'create' => CreateTaxClass::route('/create'),
            'edit' => EditTaxClass::route('/{record}/edit'),
        ];
    }
}