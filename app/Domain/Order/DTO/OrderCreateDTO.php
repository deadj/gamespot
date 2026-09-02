<?php

namespace App\Domain\Order\DTO;

use App\Application\Shared\AbstractModelDTO;
use App\Domain\Order\Enum\OrderStatus;

class OrderCreateDTO extends AbstractModelDTO 
{
    public function __construct(
        public string $publicId,
        public string $sku,
        public float $amount,
        public string $currency,
        public OrderStatus $status,
        public ?string $code = null,
    ) {}
}