<?php

namespace App\Domain\Order\DTO;

readonly class OrderDeliveryStatusResponseDTO
{
    public function __construct(
        public int $queue,
        public int $delivered,
        public int $partiallyDelivered,
    ) {}
}