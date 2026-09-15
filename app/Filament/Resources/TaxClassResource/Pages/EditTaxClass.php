<?php

namespace App\Filament\Resources\TaxClassResource\Pages;

use App\Filament\Resources\TaxClassResource;
use App\Services\DeleteTaxClasses;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditTaxClass extends BaseEditRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            // Varsayılan silme, sınıfa bağlı ürün varyantları nedeniyle
            // FK kısıtına takılıyordu. Silme işlemini bağlı varyantları
            // da temizleyen servise devrediyoruz.
            DeleteAction::make()
                ->action(function ($record): void {
                    app(DeleteTaxClasses::class)->handle(new EloquentCollection([$record]));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}