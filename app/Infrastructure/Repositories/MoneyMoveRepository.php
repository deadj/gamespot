<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Money\Repository\MoneyMoveRepositoryInterface;
use App\Infrastructure\Models\MoneyMove;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Override;

class MoneyMoveRepository extends AbstractRepository implements MoneyMoveRepositoryInterface
{
    #[Override]
    public function getByUniqueIndex(int $orderId, MoneyMoveType $type, ?int $orderItemId): ?MoneyMove
    {
        return $this->model->where([
            ['order_id', $orderId],
            ['order_item_id', $orderItemId],
            ['type', $type],
        ])->first();
    }

    #[Override]
    protected function getModel(): Model
    {
        return new MoneyMove();
    }
}