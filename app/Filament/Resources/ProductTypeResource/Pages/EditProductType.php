<?php

namespace App\Filament\Resources\ProductTypeResource\Pages;

use App\Filament\Resources\ProductTypeResource;
use App\Services\DeleteProductTypes;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProductType extends BaseEditRecord
{
    protected static string $resource = ProductTypeResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            // Varsayılan silme, türüne bağlı ürünler nedeniyle engelleniyordu
            // (before/cancel). Silme işlemini bağlı ürünleri de temizleyen
            // servise devrediyoruz.
            DeleteAction::make()
                ->action(function ($record): void {
                    app(DeleteProductTypes::class)->handle(new EloquentCollection([$record]));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}