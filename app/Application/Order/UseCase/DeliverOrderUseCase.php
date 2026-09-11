<?php

namespace App\Application\Order\UseCase;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderItemRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Shared\LoggerInterface;
use App\Infrastructure\Models\Order;

class DeliverOrderUseCase
{
    public function __construct(
        protected DeliverOrderItemUseCase $deliverOrderItemUseCase,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected LoggerInterface $logger,
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
            $this->logStatusUpdate($order->public_id, $orderOldStatus, OrderStatus::Paid);
            $orderOldStatus = $order->status;
        } 

        if ($order->status != OrderStatus::Delivering) {
            $order = $this->orderRepository->updateStatus($order, OrderStatus::Delivering);
            $this->logStatusUpdate($order->public_id, $orderOldStatus, OrderStatus::Delivering);
        }

        foreach ($order->items as $item) {
            $this->deliverOrderItemUseCase->execute($item->id, $paymentStatus);
        }

        $order = $this->handleOrderUseCaseResult($order);

        return $order;
    }

    protected function handleOrderUseCaseResult(Order $order): Order
    {
        $order->refresh();
            
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

        $order = $this->orderRepository->updateStatus($order, $resultOrderStatus);
        $this->logStatusUpdate($order->public_id, OrderStatus::Delivering, $resultOrderStatus);

        return $order;
    }

    protected function logStatusUpdate(
        string $orderPublicId, 
        OrderStatus $oldOrderStatus, 
        OrderStatus $newOrderStatus,
    ): void
    {
        $this->logger->info('Order status changed', [
            'order_public_id' => $orderPublicId,
            'from' => $oldOrderStatus->value,
            'to' => $newOrderStatus->value,
        ]);
    }
}