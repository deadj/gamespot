<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Enum\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'public_id',
        'sku',
        'amount',
        'currency',
        'status',
        'code',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];    

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}
