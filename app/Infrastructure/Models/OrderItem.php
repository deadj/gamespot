<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Enum\OrderStatus;
use App\Infrastructure\Observers\OrderItemObserver;
use App\Infrastructure\Shared\Trait\MorphClassTrait;
use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([OrderItemObserver::class])]
class OrderItem extends Model
{
    use HasFactory, MorphClassTrait;

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

    public function moneyMoves(): HasMany
    {
        return $this->hasMany(MoneyMove::class);
    }

    public function history(): MorphMany
    {
        return $this->morphMany(OrderHistory::class, 'target');
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
