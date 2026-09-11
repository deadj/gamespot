<?php

namespace App\Application\Order\UseCase;

use App\Application\Shared\Services\LogService;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderItemRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Infrastructure\Models\Order;
use App\Jobs\DeliverOrderItemJob;
use Illuminate\Support\Facades\Redis;

class DeliverOrderUseCase
{
    public function __construct(
        protected DeliverOrderItemUseCase $deliverOrderItemUseCase,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected LogService $logService,
    ) {}

    public function execute(Order $order, PaymentStatus $paymentStatus): Order
    {
        if (in_array($order->status, [
            OrderStatus::Delivered,
            OrderStatus::PartiallyDelivered,
        ])) {
            return $order; 
        }    

        $orderOldStatus = $order->status;

        if ($order->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Paid) {
            $order = $this->orderRepository->updateStatus($order, OrderStatus::Paid);
            $this->logService->logOrderStatusUpdate($order->public_id, $orderOldStatus, OrderStatus::Paid);
            $orderOldStatus = $order->status;
        } 

        if ($order->status != OrderStatus::Delivering) {
            $order = $this->orderRepository->updateStatus($order, OrderStatus::Delivering);
            $this->logService->logOrderStatusUpdate($order->public_id, $orderOldStatus, OrderStatus::Delivering);
        }

        foreach ($order->items as $item) {
            DeliverOrderItemJob::dispatch($order->id, $item->id, $paymentStatus)->onQueue('high');
            Redis::sadd('orders:in_queue', $order->id);
        }

        return $order;
    }
}