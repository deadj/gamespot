<?php

namespace App\Domain\Supplier\Repository;

use App\Domain\Shared\Repository\CreateInterface;
use App\Infrastructure\Models\Key;

interface KeyRepositoryInterface extends CreateInterface
{
    public function markForOrder(string $sku, string $orderId): ?Key;
    public function getByCode(string $code): ?Key;
    public function getByRequestId(string $orderId): ?Key;
    public function getBusy(): ?Key;
}