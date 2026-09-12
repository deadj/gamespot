<?php

namespace App\Domain\Order\DTO;

use Carbon\Carbon;

class OrderHistoryRequestDTO
{
    public function __construct(
        public int $orderId,
        public ?Carbon $dateStart = null,
        public ?Carbon $dateEnd = null, 
    ) {}
}