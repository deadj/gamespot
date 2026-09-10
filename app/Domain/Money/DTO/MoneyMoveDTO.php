<?php

namespace App\Domain\Money\DTO;

use App\Application\Shared\AbstractModelDTO;
use App\Domain\Money\Enum\MoneyMoveType;

class MoneyMoveDTO extends AbstractModelDTO
{
    public function __construct(
        public int $orderId,
        public MoneyMoveType $type,
        public float $amount,
        public ?int $orderItemId = null,
    ) {}
}