<?php

namespace App\Application\Product\UseCase;

use App\Domain\Product\Exception\ProductNotFoundException;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductStockUseCase
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
    ) {}
    
    public function execute(): Collection
    {
        $products = $this->productRepository->getStock();

        if (!$products->count() === 0)
            throw new ProductNotFoundException("products not found");

        return $products;
    }
}