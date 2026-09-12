<?php

namespace App\Infrastructure\Observers;

use App\Application\Order\Service\OrderHistoryCreateService;
use App\Infrastructure\Models\OrderItem;

class OrderItemObserver
{
    public function __construct(
        protected OrderHistoryCreateService $historyCreator,
    ) {}

    public function updated(OrderItem $item): void
    {
        $this->historyCreator->create($item);
    }

    public function created(OrderItem $item): void
    {
        $this->historyCreator->create($item);     
    }
}
