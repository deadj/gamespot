<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Supplier\Repository\KeyRepositoryInterface;
use App\Infrastructure\Models\Key;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Model;
use Override;

class KeyRepository extends AbstractRepository implements KeyRepositoryInterface
{
    #[Override]
    public function markForOrder(string $sku, string $requestId): ?Key
    {
        $key = $this->model->where([
            ['sku', $sku],
            ['request_id', null],
        ])
        ->lock('FOR UPDATE SKIP LOCKED')
        ->first();

        if (!$key)
            return null;

        $key->update([
            'request_id' => $requestId,
        ]);

        return $key;
    }

    #[Override]
    public function getByCode(string $code): ?Key
    {
        return $this->model->where('code', $code)->first();
    }

    #[Override]
    public function getByRequestId(string $requestId): ?Key
    {
        return $this->model->where('request_id', $requestId)->first();
    }

    #[Override]
    protected function getModel(): Model
    {
        return new Key();
    }
}