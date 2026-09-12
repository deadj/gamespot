<?php

namespace App\Infrastructure\Models;

use App\Domain\Order\Enum\OrderStatus;
use App\Infrastructure\Observers\OrderHistoryObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy([OrderHistoryObserver::class])]
class OrderHistory extends Model
{
    protected $fillable = [
        'target_id',
        'target_type',
        'order_id',
        'old_status',
        'new_status',
    ];

    protected $casts = [
        'old_status' => OrderStatus::class,
        'new_status' => OrderStatus::class,
    ];

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
