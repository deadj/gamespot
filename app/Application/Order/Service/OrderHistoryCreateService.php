<?php

namespace App\Application\Order\Service;

use App\Domain\Order\DTO\OrderHistoryModelDTO;
use App\Domain\Order\Repository\OrderHistoryRepositoryInterface;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Models\OrderItem;

class OrderHistoryCreateService
{
    public function __construct(
        protected OrderHistoryRepositoryInterface $historyRepository,
    ) {}

    public function create(Order|OrderItem $object): void
    {
        if ($object->getOriginal('status') != $object->status) {
            $this->historyRepository->create(new OrderHistoryModelDTO(
                targetId: $object->id,
                targetType: $object->getMorphClass(),
                orderId: $object instanceof Order ? $object->id : $object->order_id,
                oldStatus: $object->getOriginal('status'),
                newStatus: $object->status,
            ));
        }
    }
}