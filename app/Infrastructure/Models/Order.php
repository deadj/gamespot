<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Enum\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;
    
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

    protected static function newFactory()
    {
        return OrderFactory::new();
    }
}
