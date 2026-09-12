<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\DTO\OrderHistoryRequestDTO;
use App\Domain\Shared\Repository\CreateInterface;
use Illuminate\Database\Eloquent\Collection;

interface OrderHistoryRepositoryInterface extends CreateInterface
{
    public function getHistory(OrderHistoryRequestDTO $dto): Collection;
}