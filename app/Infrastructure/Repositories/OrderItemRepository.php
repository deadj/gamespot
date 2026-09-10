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
    public function updateStatus(int $itemId, OrderStatus $status): OrderItem
    {
        $order = $this->model->find($itemId);
        
        if (!$order->status->getChangePermission($status))
            throw new OrderChangeStatusException($itemId, $order->status, $status);

        $order->update(['status' => $status]);
        return $order;
    }

    #[Override]
    public function update(int $itemId, array $dataForUpdate): OrderItem
    {
        $order = $this->model->find($itemId);

        if (
            array_key_exists('status', $dataForUpdate)
            && !$order->status->getChangePermission($dataForUpdate['status'])
        ) {
            throw new OrderChangeStatusException($itemId, $order->status, $dataForUpdate['status']);
        }

        $order->update($dataForUpdate);
        return $order;
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

    protected function getModel(): Model
    {
        return new OrderItem();
    }
}