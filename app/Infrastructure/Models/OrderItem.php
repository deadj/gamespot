<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Enum\OrderStatus;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'public_id',
        'sku',
        'status',
        'amount',
        'currency',
        'code',
        'request_id',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function moneyMove(): HasOne
    {
        return $this->hasOne(MoneyMove::class);
    }

    protected function orderPublicId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->order->public_id,
        );
    }

    protected static function newFactory()
    {
        return OrderItemFactory::new();
    }
}
