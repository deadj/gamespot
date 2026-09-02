<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'event_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
