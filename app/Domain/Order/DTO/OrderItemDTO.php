<?php

namespace App\Domain\Order\DTO;

use App\Application\Shared\AbstractModelDTO;
use App\Domain\Order\Enum\OrderStatus;

class OrderItemDTO extends AbstractModelDTO 
{
    public function __construct(
        public int $orderId,
        public int $productId,
        public string $publicId,
        public string $sku,
        public OrderStatus $status,
        public float $amount,
        public string $currency,
        public ?string $code = null,
    ) {}
}