<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    protected $fillable = [
        'sku',
        'code',
        'order_id',
    ];
}
