<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Enum\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_public_id', 'public_id');
    }

    public function moneyMoves(): HasMany
    {
        return $this->hasMany(MoneyMove::class);
    }
}
