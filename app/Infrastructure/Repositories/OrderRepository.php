<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Exception\OrderChangeStatusException;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Override;

class OrderRepository extends AbstractRepository implements OrderRepositoryInterface
{
    #[Override]
    public function updateStatus(int $orderId, OrderStatus $status): Order
    {
        $order = $this->model->find($orderId);
        
        if (!$order->status->getChangePermission($status))
            throw new OrderChangeStatusException($orderId, $order->status, $status);

        $order->update(['status' => $status]);
        return $order;
    }

    #[Override]
    public function update(int $orderId, array $dataForUpdate): Order
    {
        $order = $this->model->find($orderId);

        if (
            array_key_exists('status', $dataForUpdate)
            && !$order->status->getChangePermission($dataForUpdate['status'])
        ) {
            throw new OrderChangeStatusException($orderId, $order->status, $dataForUpdate['status']);
        }

        $order->update($dataForUpdate);
        return $order;
    }
    
    #[Override]
    public function getStrangeOrders(): Collection
    {
        return $this->model
            ->with('payment')
            ->where(function (Builder $query) {
                $query->where('status', '!=', OrderStatus::Delivered)
                    ->whereHas('payment', function (Builder $q) {
                        $q->where('status', PaymentStatus::Paid);
                    });
            })
            ->orWhere(function (Builder $query) {
                $query->where('status', OrderStatus::Delivered)
                    ->whereHas('payment', function (Builder $q) {
                        $q->where('status', PaymentStatus::Failed);
                    });
            })
            ->get();
    }

    #[Override]
    public function getByStatuses(array $statuses): Collection
    {
        return $this->model->with('payment')->whereIn('status', $statuses)->get();
    }

    #[Override]
    public function getById(int $orderId): ?Order
    {
        return $this->model->with('payment')->find($orderId);   
    }

    #[Override]
    public function getByIdForUpdate(int $orderId): ?Order
    {
        return $this->model
            ->where('id', $orderId)
            ->with('payment')
            ->lockForUpdate()
            ->first();
    }

    #[Override]
    public function getByPublicId(string $publicId): ?Order
    {
        return $this->model->where('public_id', $publicId)->first();
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