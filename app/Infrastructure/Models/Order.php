<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Enum\OrderStatus;
use App\Infrastructure\Observers\OrderObserver;
use App\Infrastructure\Shared\Trait\MorphClassTrait;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    use HasFactory, MorphClassTrait;
    
    protected $fillable = [
        'public_id',
        'status',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
    ];    

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_public_id', 'public_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function moneyMoves(): HasMany
    {
        return $this->hasMany(MoneyMove::class);
    }    
    
    public function history(): MorphMany
    {
        return $this->morphMany(OrderHistory::class, 'target');
    }

    protected static function newFactory()
    {
        return OrderFactory::new();
    }
}
