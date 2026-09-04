<?php

namespace App\Infrastructure\Observers;

use App\Infrastructure\Models\MoneyMove;
use Exception;

class MoneyMoveObserver
{
    public function updating(MoneyMove $moneyMove): void
    {
        throw new Exception('money move updating disabled');
    }

    public function deleting(MoneyMove $moneyMove): void
    {
        throw new Exception('money move deleting disabled');
    }    
}
