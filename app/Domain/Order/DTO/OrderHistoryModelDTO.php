<?php

namespace App\Domain\Order\DTO;

use App\Application\Shared\AbstractModelDTO;
use App\Domain\Order\Enum\OrderStatus;

class OrderHistoryModelDTO extends AbstractModelDTO
{
    public function __construct(
        public int $targetId,
        public string $targetType,
        public int $orderId,
        public OrderStatus $newStatus,
        public ?OrderStatus $oldStatus = null,
) {}
}