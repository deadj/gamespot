<?php

namespace App\Application\Product\UseCase;

use App\Domain\Product\Repository\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductsAllUseCase
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
    ) {}

    public function execute(): Collection
    {
         return $this->productRepository->getAll();
    }
}
