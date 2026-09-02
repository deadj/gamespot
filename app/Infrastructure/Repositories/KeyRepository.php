<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Order\Repository\KeyRepositoryInterface;
use App\Infrastructure\Models\Key;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Override;

class KeyRepository extends AbstractRepository implements KeyRepositoryInterface
{
    #[Override]
    public function markForOrder(string $sku, string $orderId): ?Key
    {
        $key = $this->model->where([
            ['sku', $sku],
            ['order_id', null],
        ])
        ->lock('FOR UPDATE SKIP LOCKED')
        ->first();

        if (!$key)
            return null;

        $key->update([
            'order_id' => $orderId,
        ]);

        return $key;
    }

    #[Override]
    public function getByCode(string $code): ?Key
    {
        return $this->model->where('code', $code)->first();
    }

    #[Override]
    public function getByOrderId(string $orderId): ?Key
    {
        return $this->model->where('order_id', $orderId)->first();
    }

    #[Override]
    protected function getModel(): Model
    {
        return new Key();
    }
}