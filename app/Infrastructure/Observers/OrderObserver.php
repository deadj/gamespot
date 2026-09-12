<?php

namespace App\Infrastructure\Observers;

use App\Application\Order\Service\OrderHistoryCreateService;
use App\Infrastructure\Models\Order;

class OrderObserver
{
    public function __construct(
        protected OrderHistoryCreateService $historyCreator,
    ) {}

    public function updated(Order $order): void
    {
        $this->historyCreator->create($order);
    }

    public function created(Order $order): void
    {
        $this->historyCreator->create($order);     
    }
}
