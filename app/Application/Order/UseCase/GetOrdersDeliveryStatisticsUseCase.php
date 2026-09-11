<?php

namespace App\Application\Order\UseCase;

use App\Domain\Order\DTO\OrderDeliveryStatusResponseDTO;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use Illuminate\Support\Facades\Redis;

class GetOrdersDeliveryStatisticsUseCase
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
    ) {}

    public function execute(): OrderDeliveryStatusResponseDTO
    {
        $ordersInQueue = Redis::scard('orders:in_queue');
        
        $deliveredOrders = $this->orderRepository->getByStatuses([
            OrderStatus::Delivered,
            OrderStatus::PartiallyDelivered,
        ]);

        return new OrderDeliveryStatusResponseDTO(
            queue: $ordersInQueue,
            delivered: $deliveredOrders->where('status', OrderStatus::Delivered)->count(),
            partiallyDelivered: $deliveredOrders->where('status', OrderStatus::PartiallyDelivered)->count(),
        );
    }
}