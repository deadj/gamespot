<?php

namespace App\Domain\Money\Repository;

use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Shared\Repository\CreateInterface;
use App\Infrastructure\Models\MoneyMove;

interface MoneyMoveRepositoryInterface extends CreateInterface{
    public function getByUniqueIndex(int $orderId, MoneyMoveType $type, ?int $orderItemId): ?MoneyMove;
}