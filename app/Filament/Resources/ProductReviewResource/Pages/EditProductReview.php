<?php

namespace App\Filament\Resources\ProductReviewResource\Pages;

use App\Filament\Resources\ProductReviewResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProductReview extends BaseEditRecord
{
    protected static string $resource = ProductReviewResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
