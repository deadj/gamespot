<?php

namespace App\Domain\Order\DTO;

use Illuminate\Database\Eloquent\Collection;

readonly class OrderHistoryResponseDTO
{
    public function __construct(
        public array $orderState,
        public array $orderItemsState,
        public Collection $history, 
        public array $financialReport,
    ) {}
}