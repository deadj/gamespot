<?php

namespace App\Infrastructure\Models;

use App\Domain\Payment\Enum\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

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
}
