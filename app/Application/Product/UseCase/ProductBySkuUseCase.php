<?php

namespace App\Application\Product\UseCase;

use App\Domain\Product\Exception\ProductNotFoundException;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Infrastructure\Models\Product;

class ProductBySkuUseCase
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
    ) {}

    public function execute(string $sku): Product
    {
        $product = $this->productRepository->getBySku($sku);

        if (!$product)
            throw new ProductNotFoundException("product $sku not found");

        return $product;        
    }
}