<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Infrastructure\Models\Product;
use App\Infrastructure\Shared\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Override;

class ProductRepository extends AbstractRepository implements ProductRepositoryInterface
{
    #[Override]
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    #[Override]
    public function getBySku(string $sku): ?Product
    {
        return $this->model->where('sku', $sku)->first();
    }

    #[Override]
    public function getStock(): Collection
    {
        return $this->model
            ->withCount(['keys as keys_stock_count' => function (Builder $query) {
                $query->whereNull('request_id');
            }])
            ->get();
    }

    #[Override]
    protected function getModel(): Model
    {
        return new Product();
    }
}