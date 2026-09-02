<?php

namespace App\Domain\Order\Repository;

use App\Domain\Shared\Repository\CreateInterface;
use App\Infrastructure\Models\Key;

interface KeyRepositoryInterface extends CreateInterface
{
    public function markForOrder(string $sku, string $orderId): ?Key;
    public function getByCode(string $code): ?Key;
    public function getByOrderId(string $orderId): ?Key;
}