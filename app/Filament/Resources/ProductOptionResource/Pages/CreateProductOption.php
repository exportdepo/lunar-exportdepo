<?php

namespace App\Filament\Resources\ProductOptionResource\Pages;

use App\Filament\Resources\ProductOptionResource;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages\CreateProductOption as LunarCreateProductOption;

class CreateProductOption extends LunarCreateProductOption
{
    protected static string $resource = ProductOptionResource::class;
}