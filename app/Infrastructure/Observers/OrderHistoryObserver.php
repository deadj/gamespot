<?php

namespace App\Infrastructure\Observers;

use App\Infrastructure\Models\OrderHistory;
use Exception;

class OrderHistoryObserver
{
    public function updating(OrderHistory $history): void
    {
        throw new Exception('money move updating disabled');
    }

    public function deleting(OrderHistory $history): void
    {
        throw new Exception('money move deleting disabled');
    }  
}
