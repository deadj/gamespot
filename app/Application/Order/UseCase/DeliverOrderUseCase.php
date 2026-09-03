<?php

namespace App\Application\Order\UseCase;

use App\Application\Order\Service\SupplierHandler;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Supplier\Repository\KeyRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Supplier\Enum\SupplierReason;
use App\Domain\Supplier\Enum\SupplierStatus;
use App\Infrastructure\Models\Order;

class DeliverOrderUseCase
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected KeyRepositoryInterface $keyRepository,
        protected SupplierHandler $supplierHandler
    ) {}

    public function execute(Order $order, PaymentStatus $paymentStatus): Order
    {
        if ($order->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Paid) {
            $this->orderRepository->updateStatus($order->id, OrderStatus::Paid);
        } elseif ($order->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Failed) {
            $order = $this->orderRepository->updateStatus($order->id, OrderStatus::PaymentFailed);
            return $order;
        } else {
            return $order;
        }

        if ($order->code)
            return $this->orderRepository->updateStatus($order->id, OrderStatus::Delivered);

        $order = $this->orderRepository->updateStatus($order->id, OrderStatus::Delivering);

        $supplierResponse = $this->supplierHandler->getResponse(
            sku: $order->sku,
            orderPublicId: $order->public_id,
        );

        if ($supplierResponse->status == SupplierStatus::Ok->value) {
            return $this->orderRepository->update($order->id, [
                'status' => OrderStatus::Delivered,
                'code' => $supplierResponse->code,
            ]);
        }

        if ($supplierResponse->reason == SupplierReason::OutOfStock->value)
            return $this->orderRepository->updateStatus($order->id, OrderStatus::OutOfStock);

        return $this->orderRepository->updateStatus($order->id, OrderStatus::DeliveryFailed);        
    }
}