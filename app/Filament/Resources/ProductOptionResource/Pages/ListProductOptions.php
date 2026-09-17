<?php

namespace App\Filament\Resources\ProductOptionResource\Pages;

use App\Filament\Resources\ProductOptionResource;
use Lunar\Admin\Filament\Resources\ProductOptionResource\Pages\ListProductOptions as LunarListProductOptions;

class ListProductOptions extends LunarListProductOptions
{
    protected static string $resource = ProductOptionResource::class;
}