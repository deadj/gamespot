<?php

namespace App\Infrastructure\Models;

use App\Domain\Product\Enum\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'name',
        'type',
        'price',
        'currency',
        'image',
    ];

    protected $casts = [
        'type' => ProductType::class,
    ];

    public function keys(): HasMany
    {
        return $this->hasMany(Key::class, 'sku', 'sku');
    }
}
