<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    protected $fillable = [
        'sku',
        'code',
        'request_id',
    ];
}
