<?php

namespace App\Http\Controllers;

use App\Application\Order\UseCase\GetOrderUseCase;
use App\Application\Order\UseCase\GetStrangeOrdersUseCase;
use App\Application\Order\UseCase\OrderCreateUseCase;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Product\Exception\AllProductsNotFoundException;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function store(
        OrderCreateRequest $request,
        OrderCreateUseCase $useCase,
    ): OrderResource|JsonResponse
    {
        try {
            $order = $useCase->execute($request->skus);
            return new OrderResource($order);
        } catch (ProductNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 200);
        } catch (AllProductsNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);            
        }
    }

    public function show(
        int $id,
        GetOrderUseCase $useCase,
    ): OrderResource|JsonResponse
    {
        try {
            $order = $useCase->execute($id);
            return new OrderResource($order);
        } catch (OrderNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    public function showStrangeOrders(
        GetStrangeOrdersUseCase $useCase,
    ): AnonymousResourceCollection
    {
        $orders = $useCase->execute();
        return OrderResource::collection($orders);
    }
}
