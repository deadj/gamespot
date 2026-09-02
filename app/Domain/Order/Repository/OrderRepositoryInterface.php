<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Shared\Repository\CreateInterface;
use App\Infrastructure\Models\Order;

interface OrderRepositoryInterface extends CreateInterface
{
    public function updateStatus(int $orderId, OrderStatus $status): Order;
    public function update(int $orderId, array $dataForUpdate): Order;
    public function getById(int $orderId): ?Order;
    public function getByIdForUpdate(int $orderId): ?Order;
    public function getByPublicIdForUpdate(string $publicId): ?Order;
}