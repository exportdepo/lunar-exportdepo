<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Models\Product;

class ProductReview extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'name',
        'rating',
        'title',
        'body',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Filament kolonlarında ürün adını Lunar attribute'u üzerinden göstermek için.
     */
    public function getProductTitleAttribute(): string
    {
        return (string) ($this->product?->translateAttribute('name') ?? ('Ürün #'.$this->product_id));
    }
}
