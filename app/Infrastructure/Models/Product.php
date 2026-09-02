<?php

namespace App\Infrastructure\Models;

use App\Domain\Product\Enum\ProductType;
use Illuminate\Database\Eloquent\Model;

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
}
