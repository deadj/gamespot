<?php

namespace App\Infrastructure\Models;

use App\Domain\Payment\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'event_id',
        'order_public_id',
        'status',
        'amount',
        'currency',
        'event_created_at',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'event_created_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_public_id', 'public_id');
    }
}
