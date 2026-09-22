<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Services\DeleteCustomers;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Lunar\Admin\Filament\Resources\CustomerResource\Pages\ViewCustomer as BaseViewCustomer;

class ViewCustomer extends BaseViewCustomer
{
    protected static string $resource = CustomerResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            // Varsayılan silme FK kısıtları nedeniyle başarısız oluyordu;
            // silme işlemini bağlantıları da temizleyen servise devrediyoruz.
            DeleteAction::make()
                ->action(fn ($record) => app(DeleteCustomers::class)->handle([$record])),
            EditAction::make(),
        ];
    }
}
