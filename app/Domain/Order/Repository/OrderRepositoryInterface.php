<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Shared\Repository\CreateInterface;
use App\Infrastructure\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface extends CreateInterface
{
    public function updateStatus(Order $order, OrderStatus $status): Order;
    public function update(Order $order, array $dataForUpdate): Order;
    public function getStrangeOrders(): Collection;
    public function getByStatuses(array $statuses): Collection;
    public function getById(int $orderId): ?Order;
    public function getByIdForUpdate(int $orderId): ?Order;
    public function getByPublicId(string $publicId): ?Order;
    public function getByPublicIdForUpdate(string $publicId): ?Order;
}