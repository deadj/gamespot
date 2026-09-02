<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Override;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    #[Override]
    public function updateStatus(int $orderId, OrderStatus $status): Order
    {
        $order = $this->model->find($orderId);
        $order->update(['status' => $status]);
        return $order;
    }

    #[Override]
    public function update(int $orderId, array $dataForUpdate): Order
    {
        $order = $this->model->find($orderId);
        $order->update($dataForUpdate);
        return $order;
    }

    #[Override]
    public function getById(int $orderId): ?Order
    {
        return $this->model->find($orderId);   
    }

    #[Override]
    public function getByIdForUpdate(int $orderId): ?Order
    {
        return $this->model
            ->where('id', $orderId)
            ->lockForUpdate()
            ->first();
    }

    #[Override]
    public function getByPublicIdForUpdate(string $publicId): ?Order
    {
        return $this->model
            ->where('public_id', $publicId)
            ->lockForUpdate()
            ->first();
    }

    protected function getModel(): Model
    {
        return new Order();
    }
}