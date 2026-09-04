<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Money\Repository\MoneyMoveRepositoryInterface;
use App\Infrastructure\Models\MoneyMove;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Override;

class MoneyMoveRepository extends AbstractRepository implements MoneyMoveRepositoryInterface
{
    #[Override]
    protected function getModel(): Model
    {
        return new MoneyMove();
    }
}