<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Shared\Repository\CreateInterface;
use App\Infrastructure\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;

interface OrderItemRepositoryInterface extends CreateInterface
{
    public function updateStatus(int $orderId, OrderStatus $status): OrderItem;
    public function update(int $itemId, array $dataForUpdate): OrderItem;
    public function getByStatuses(array $statuses): Collection;
    public function getById(int $itemId): ?OrderItem;
    public function getByIdForUpdate(int $itemId): ?OrderItem;
    public function getNotDelivetedByOrderId(int $orderId): Collection;
}