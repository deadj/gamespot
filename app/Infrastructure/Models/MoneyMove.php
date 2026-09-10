<?php

namespace App\Infrastructure\Models;

use App\Domain\Money\Enum\MoneyMoveType;
use App\Infrastructure\Observers\MoneyMoveObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([MoneyMoveObserver::class])]
class MoneyMove extends Model
{
    protected $fillable = [
        'order_id',
        'order_item_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'type' => MoneyMoveType::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
