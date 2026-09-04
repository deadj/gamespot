<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Key extends Model
{
    protected $fillable = [
        'sku',
        'code',
        'request_id',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}
