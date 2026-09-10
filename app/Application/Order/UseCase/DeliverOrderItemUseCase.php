<?php

namespace App\Application\Order\UseCase;

use App\Application\Money\UseCase\CreateMoneyMoveUseCase;
use App\Application\Order\Service\SupplierHandler;
use App\Domain\Money\DTO\MoneyMoveDTO;
use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderItemRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Shared\LoggerInterface;
use App\Domain\Supplier\Enum\SupplierReason;
use App\Domain\Supplier\Enum\SupplierStatus;
use App\Domain\Supplier\Repository\KeyRepositoryInterface;
use App\Infrastructure\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DeliverOrderItemUseCase
{
    public function __construct(
        protected CreateMoneyMoveUseCase $createMoneyMoveUseCase,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected KeyRepositoryInterface $keyRepository,
        protected SupplierHandler $supplierHandler,
        protected LoggerInterface $logger,
    ) {}

    public function execute(
        int $orderItemId, 
        PaymentStatus $paymentStatus
    ): OrderItem
    {
        return DB::transaction(function () use ($orderItemId, $paymentStatus) {
            $item = $this->orderItemRepository->getByIdForUpdate($orderItemId);
            $oldOrderItemStatus = $item->status;

            if ($item->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Paid) {
                $item = $this->orderItemRepository->updateStatus($item->id, OrderStatus::Paid);
                $this->logStatusUpdate($item, $oldOrderItemStatus, OrderStatus::Paid);
                $oldOrderItemStatus = $item->status;
            } elseif ($item->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Failed) {
                $item = $this->orderItemRepository->updateStatus($item->id, OrderStatus::PaymentFailed);
                $this->logStatusUpdate($item, $oldOrderItemStatus, OrderStatus::PaymentFailed);

                return $item;
            } 

            if ($item->code) {
                if ($item->status != OrderStatus::Delivered) {
                    $item = $this->orderItemRepository->updateStatus($item->id, OrderStatus::Delivered);
                    $this->logStatusUpdate($item, $oldOrderItemStatus, OrderStatus::Delivered);
                }

                return $item;
            }

            if ($item->status != OrderStatus::Delivering) {
                $item = $this->orderItemRepository->updateStatus($item->id, OrderStatus::Delivering);
                $this->logStatusUpdate($item, $oldOrderItemStatus, OrderStatus::Delivering);
                $oldOrderItemStatus = $item->status;
            }

            $supplierResponse = $this->supplierHandler->getResponse(
                sku: $item->sku,
                orderItemPublicId: $item->public_id,
            );

            if ($supplierResponse->status == SupplierStatus::Ok->value) {
                $this->logger->info("Key received", [
                    'order_item_public_id' => $item->public_id,
                    'order_public_id' => $item->orderPublicId,
                    'code' => $supplierResponse->code,
                ]);

                $item = $this->orderItemRepository->update($item->id, [
                    'status' => OrderStatus::Delivered,
                    'code' => $supplierResponse->code,
                ]);

                $this->logStatusUpdate($item, $oldOrderItemStatus, OrderStatus::Delivered);

                $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
                    orderId: $item->order_id,
                    orderItemId: $item->id,
                    type: MoneyMoveType::Issued,
                    amount: $item->amount,
                ));

                return $item;
            }

            if ($supplierResponse->reason == SupplierReason::OutOfStock->value) {
                $this->logger->warning('Out of stock', [
                    'order_item_public_id' => $item->public_id,
                    'order_public_id' => $item->orderPublicId,
                    'sku' => $item->sku,
                ]);
                $item = $this->orderItemRepository->updateStatus($item->id, OrderStatus::OutOfStock);
                $this->logStatusUpdate($item, $oldOrderItemStatus, OrderStatus::OutOfStock);

                $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
                    orderId: $item->order_id,
                    orderItemId: $item->id,
                    type: MoneyMoveType::Refund,
                    amount: $item->amount,
                ));

                return $item;
            }

            $item = $this->orderItemRepository->updateStatus($item->id, OrderStatus::DeliveryFailed);
            $this->logger->error('Delivery failed', [
                'order_item_public_id' => $item->public_id,
                'order_public_id' => $item->orderPublicId,
                'reason' => $supplierResponse->reason,
            ]);    
            $this->logStatusUpdate($item, $oldOrderItemStatus, OrderStatus::DeliveryFailed);

            $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
                orderId: $item->order_id,
                orderItemId: $item->id,
                type: MoneyMoveType::Refund,
                amount: $item->amount,
            ));

            return $item;
        });
    }

    protected function logStatusUpdate(
        OrderItem $item, 
        OrderStatus $oldOrderStatus, 
        OrderStatus $newOrderStatus,
    ): void
    {
        $this->logger->info('Order item status changed', [
            'order_item_public_id' => $item->public_id,
            'order_public_id' => $item->orderPublicId,
            'from' => $oldOrderStatus->value,
            'to' => $newOrderStatus->value,
        ]);
    }    
}