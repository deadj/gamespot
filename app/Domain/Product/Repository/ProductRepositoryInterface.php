<?php

namespace App\Domain\Product\Repository;

use App\Infrastructure\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function getAll(): Collection;
    public function getBySku(string $sku): ?Product;   
}