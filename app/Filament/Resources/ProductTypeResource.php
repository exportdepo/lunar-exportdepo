<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductTypeResource\Pages\CreateProductType;
use App\Filament\Resources\ProductTypeResource\Pages\EditProductType;
use App\Filament\Resources\ProductTypeResource\Pages\ListProductTypes;
use App\Services\DeleteProductTypes;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Admin\Filament\Resources\ProductTypeResource as LunarProductTypeResource;

class ProductTypeResource extends LunarProductTypeResource
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
                        ->successNotificationTitle('Ürün türleri silindi.')
                        ->action(function (EloquentCollection $records): void {
                            app(DeleteProductTypes::class)->handle($records);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListProductTypes::route('/'),
            'create' => CreateProductType::route('/create'),
            'edit' => EditProductType::route('/{record}/edit'),
        ];
    }
}