<?php

namespace App\Http\Controllers;

use App\Application\Order\UseCase\GetOrdersDeliveryStatisticsUseCase;
use App\Application\Order\UseCase\GetOrderUseCase;
use App\Application\Order\UseCase\GetStrangeOrdersUseCase;
use App\Application\Order\UseCase\OrderCreateUseCase;
use App\Application\Order\UseCase\OrderHistoryUseCase;
use App\Domain\Order\DTO\OrderHistoryRequestDTO;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Product\Exception\AllProductsNotFoundException;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderHistoryRequest;
use App\Http\Resources\OrderHistoryResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrdersDeliveryStatisticsResource;
use Carbon\Carbon;
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

    public function showOrderQueue(
        GetOrdersDeliveryStatisticsUseCase $useCase,
    ): OrdersDeliveryStatisticsResource
    {
        $statistics = $useCase->execute();
        return new OrdersDeliveryStatisticsResource($statistics);
    }

    public function showHistory(
        OrderHistoryRequest $request,
        OrderHistoryUseCase $useCase,
    ): OrderHistoryResource|JsonResponse
    {
        try {
            $historyData = $useCase->execute(new OrderHistoryRequestDTO(
                orderId: $request->order_id,
                dateStart: $request->date_start ? Carbon::parse($request->date_start) : null,
                dateEnd: $request->date_end ? Carbon::parse($request->date_end) : null,
            ));
            return new OrderHistoryResource($historyData);        
        } catch (OrderNotFoundException $e) {
            return response()->json($e->getMessage(), 404);
        }
    }
}
