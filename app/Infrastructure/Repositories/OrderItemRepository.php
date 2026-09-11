<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Exception\OrderChangeStatusException;
use App\Domain\Order\Repository\OrderItemRepositoryInterface;
use App\Infrastructure\Models\OrderItem;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Override;

class OrderItemRepository extends AbstractRepository implements OrderItemRepositoryInterface
{
    #[Override]
    public function updateStatus(OrderItem $item, OrderStatus $status): OrderItem
    {        
        if (!$item->status->getChangePermission($status))
            throw new OrderChangeStatusException($item->id, $item->status, $status);

        $item->update(['status' => $status]);
        return $item;
    }

    #[Override]
    public function update(OrderItem $item, array $dataForUpdate): OrderItem
    {
        if (
            array_key_exists('status', $dataForUpdate)
            && !$item->status->getChangePermission($dataForUpdate['status'])
        ) {
            throw new OrderChangeStatusException($item->id, $item->status, $dataForUpdate['status']);
        }

        $item->update($dataForUpdate);
        return $item;
    }

    #[Override]
    public function getByStatuses(array $statuses): Collection
    {
        return $this->model
            // ->with('payment')
            ->whereIn('status', $statuses)->get();
    }

    #[Override]
    public function getById(int $itemId): ?OrderItem
    {
        return $this->model
            // ->with('payment')
            ->find($itemId);   
    }

    #[Override]
    public function getByIdForUpdate(int $itemId): ?OrderItem
    {
        return $this->model
            ->where('id', $itemId)
            // ->with('payment')
            ->lockForUpdate()
            ->first();
    }

    #[Override]
    public function getNotDelivetedByOrderId(int $orderId): Collection
    {
        return $this->model->where([
            ['order_id', $orderId],
            ['status', '!=', OrderStatus::Delivered]
        ])->get();
    }

    #[Override]
    public function getByCode(string $code): ?OrderItem
    {
        return $this->model->where('code', $code)->first();
    }

    protected function getModel(): Model
    {
        return new OrderItem();
    }
}