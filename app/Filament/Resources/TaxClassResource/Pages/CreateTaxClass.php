<?php

namespace App\Filament\Resources\TaxClassResource\Pages;

use App\Filament\Resources\TaxClassResource;
use Lunar\Admin\Support\Pages\BaseCreateRecord;

class CreateTaxClass extends BaseCreateRecord
{
    protected static string $resource = TaxClassResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}