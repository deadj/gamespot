<?php

namespace App\Application\Order\UseCase;

use App\Application\Shared\Services\LogService;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HandleOrderResultUseCase
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected LogService $logService,
    ) {}

    public function execute(int $orderId): void
    {
        DB::transaction(function() use ($orderId) {
            $order = $this->orderRepository->getByIdForUpdate($orderId);

            $notFinishedItemsExists = $order->items()
                ->whereIn('status', OrderStatus::getNotFinishedStatusesForItem())
                ->exists();

            if ($notFinishedItemsExists)
                return;

            $itemsCount = $order->items()->count();
            $deliveredCount = 0;
            $notDeliveredCount = 0;

            foreach ($order->items as $item) {
                if ($item->status == OrderStatus::Delivered) {
                    $deliveredCount++;
                } else {
                    $notDeliveredCount++;
                }            
            }

            if ($deliveredCount == $itemsCount) {
                $resultOrderStatus = OrderStatus::Delivered;
            } elseif ($deliveredCount > 0) {
                $resultOrderStatus = OrderStatus::PartiallyDelivered;
            } else {
                $resultOrderStatus = OrderStatus::DeliveryFailed;
            }

            Redis::srem('orders:in_queue', $orderId);

            $order = $this->orderRepository->updateStatus($order, $resultOrderStatus);
            $this->logService->logOrderStatusUpdate($order->public_id, OrderStatus::Delivering, $resultOrderStatus);
        });
    }
}