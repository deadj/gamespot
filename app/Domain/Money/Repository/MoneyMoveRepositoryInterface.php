<?php

namespace App\Domain\Money\Repository;

use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Order\DTO\OrderHistoryRequestDTO;
use App\Domain\Shared\Repository\CreateInterface;
use App\Infrastructure\Models\MoneyMove;
use Illuminate\Database\Eloquent\Collection;

interface MoneyMoveRepositoryInterface extends CreateInterface{
    public function getForHistory(OrderHistoryRequestDTO $dto): Collection;
    public function getByUniqueIndex(int $orderId, MoneyMoveType $type, ?int $orderItemId): ?MoneyMove;
}