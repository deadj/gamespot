<?php

namespace App\Application\Order\UseCase;

use App\Application\Money\UseCase\CreateMoneyMoveUseCase;
use App\Application\Order\Service\SupplierHandler;
use App\Domain\Money\DTO\MoneyMoveDTO;
use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Supplier\Repository\KeyRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Shared\LoggerInterface;
use App\Domain\Supplier\Enum\SupplierReason;
use App\Domain\Supplier\Enum\SupplierStatus;
use App\Infrastructure\Models\Order;

class DeliverOrderUseCase
{
    public function __construct(
        protected CreateMoneyMoveUseCase $createMoneyMoveUseCase,
        protected OrderRepositoryInterface $orderRepository,
        protected KeyRepositoryInterface $keyRepository,
        protected SupplierHandler $supplierHandler,
        protected LoggerInterface $logger,
    ) {}

    public function execute(Order $order, PaymentStatus $paymentStatus): Order
    {
        $oldOrderStatus = $order->status;

        if ($order->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Paid) {
            $order = $this->orderRepository->updateStatus($order->id, OrderStatus::Paid);
            $this->logStatusUpdate($order->public_id, $oldOrderStatus, OrderStatus::Paid);
            $oldOrderStatus = $order->status;
        } elseif ($order->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Failed) {
            $order = $this->orderRepository->updateStatus($order->id, OrderStatus::PaymentFailed);
            $this->logStatusUpdate($order->public_id, $oldOrderStatus, OrderStatus::PaymentFailed);

            return $order;
        } 

        if ($order->code) {
            $order = $this->orderRepository->updateStatus($order->id, OrderStatus::Delivered);
            $this->logStatusUpdate($order->public_id, $oldOrderStatus, OrderStatus::Delivered);
            
            return $order;
        }

        if ($order->status !== OrderStatus::Delivering) {
            $order = $this->orderRepository->updateStatus($order->id, OrderStatus::Delivering);
            $this->logStatusUpdate($order->public_id, $oldOrderStatus, OrderStatus::Delivering);
            $oldOrderStatus = $order->status;
        }

        $supplierResponse = $this->supplierHandler->getResponse(
            sku: $order->sku,
            orderPublicId: $order->public_id,
        );

        if ($supplierResponse->status == SupplierStatus::Ok->value) {
            $this->logger->info("Key received", [
                'order_public_id' => $order->public_id,
                'code' => $supplierResponse->code,
            ]);

            $order = $this->orderRepository->update($order->id, [
                'status' => OrderStatus::Delivered,
                'code' => $supplierResponse->code,
            ]);

            $this->logStatusUpdate($order->public_id, $oldOrderStatus, OrderStatus::Delivered);

            $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
                orderId: $order->id,
                type: MoneyMoveType::Issued,
                amount: $order->amount,
            ));

            return $order;
        }

        if ($supplierResponse->reason == SupplierReason::OutOfStock->value) {
           $this->logger->warning('Out of stock', [
                'order_public_id' => $order->public_id,
                'sku' => $order->sku,
            ]);
            $order = $this->orderRepository->updateStatus($order->id, OrderStatus::OutOfStock);
            $this->logStatusUpdate($order->public_id, $oldOrderStatus, OrderStatus::OutOfStock);

            return $order;
        }

        $order = $this->orderRepository->updateStatus($order->id, OrderStatus::DeliveryFailed);
        $this->logger->error('Delivery failed', [
            'order_public_id' => $order->public_id,
            'reason' => $supplierResponse->reason,
        ]);    
        $this->logStatusUpdate($order->public_id, $oldOrderStatus, OrderStatus::DeliveryFailed);

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