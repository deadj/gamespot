<?php

namespace App\Application\Shared\Services;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Shared\LoggerInterface;
use App\Infrastructure\Models\OrderItem;

class LogService
{
    public function __construct(
        protected LoggerInterface $logger,
    ) {}

    public function logOrderStatusUpdate(
        string $orderPublicId, 
        OrderStatus $oldOrderStatus, 
        OrderStatus $newOrderStatus,
    ): void
    {
        $this->logger->info('Order status changed', [
            'order_public_id' => $orderPublicId,
            'from' => $oldOrderStatus->value,
            'to' => $newOrderStatus->value,
        ]);
    }

    public function logOrderItemStatusUpdate(
        OrderItem $item, 
        OrderStatus $oldOrderStatus, 
        OrderStatus $newOrderStatus,
    ): void
    {
        $this->logger->info('Order item status changed', [
            'order_item_public_id' => $item->public_id,
            'order_public_id' => $item->orderPublicId,
            'from' => $oldOrderStatus->value,
            'to' => $newOrderStatus->value,
        ]);
    }  
}