<?php

namespace App\Http\Controllers;

use App\Application\Product\UseCase\ProductBySkuUseCase;
use App\Application\Product\UseCase\ProductsAllUseCase;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function getAll(ProductsAllUseCase $useCase): AnonymousResourceCollection
    {
        return ProductResource::collection($useCase->execute());
    }

    public function getBySku(string $sku, ProductBySkuUseCase $useCase): ProductResource|JsonResponse
    {
        try {
            return new ProductResource($useCase->execute($sku));
        } catch (ProductNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}