<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Order\DTO\OrderHistoryRequestDTO;
use App\Domain\Order\Repository\OrderHistoryRepositoryInterface;
use App\Infrastructure\Models\OrderHistory;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Override;

class OrderHistoryRepository extends AbstractRepository implements OrderHistoryRepositoryInterface
{
    #[Override]
    public function getHistory(OrderHistoryRequestDTO $dto): Collection
    {
        $query = $this->model->where('order_id', $dto->orderId)->with(['target']);
        if ($dto->dateStart) $query->where('created_at', '>=', $dto->dateStart);
        if ($dto->dateEnd) $query->where('created_at', '<=', $dto->dateEnd);          
        
        return $query->orderBy('id', 'asc')->get();
    }

    #[Override]
    protected function getModel(): Model
    {
        return new OrderHistory();
    }
}