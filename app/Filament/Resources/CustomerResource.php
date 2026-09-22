<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages\CreateCustomer;
use App\Filament\Resources\CustomerResource\Pages\EditCustomer;
use App\Filament\Resources\CustomerResource\Pages\ListCustomers;
use App\Filament\Resources\CustomerResource\Pages\ViewCustomer;
use App\Services\DeleteCustomers;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\CustomerResource as LunarCustomerResource;

/**
 * Lunar CustomerResource override'ı: varsayılan silme aksiyonları FK kısıtları
 * (sipariş/sepet/adres/pivot) nedeniyle başarısız oluyordu. Silme işlemini
 * bağlantıları da temizleyen DeleteCustomers servisine devreder.
 */
class CustomerResource extends LunarCustomerResource
{
    public static function table(Table $table): Table
    {
        return parent::table($table)
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                    ->action(fn (Model $record) => app(DeleteCustomers::class)->handle([$record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(fn ($records) => app(DeleteCustomers::class)->handle($records)),
                ]),
            ]);
    }

    public static function getDefaultPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
            'view' => ViewCustomer::route('/{record}'),
        ];
    }
}
