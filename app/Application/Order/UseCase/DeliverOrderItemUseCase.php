<?php

namespace App\Application\Order\UseCase;

use App\Application\Money\UseCase\CreateMoneyMoveUseCase;
use App\Application\Order\Service\SupplierHandler;
use App\Application\Shared\Services\LogService;
use App\Domain\Money\DTO\MoneyMoveDTO;
use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderItemRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Shared\LoggerInterface;
use App\Domain\Supplier\DTO\SupplierClientResponseDTO;
use App\Domain\Supplier\Enum\SupplierReason;
use App\Domain\Supplier\Enum\SupplierStatus;
use App\Infrastructure\Models\OrderItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DeliverOrderItemUseCase
{
    public function __construct(
        protected CreateMoneyMoveUseCase $createMoneyMoveUseCase,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected SupplierHandler $supplierHandler,
        protected LogService $logService,
        protected LoggerInterface $logger,
    ) {}

    public function execute(
        int $orderItemId, 
        PaymentStatus $paymentStatus
    ): ?SupplierClientResponseDTO
    {
        return DB::transaction(function () use ($orderItemId, $paymentStatus) {
            $item = $this->orderItemRepository->getByIdForUpdate($orderItemId);
            $oldOrderItemStatus = $item->status;

            if ($this->returnResultBeforeSupplierResponse($item, $paymentStatus, $oldOrderItemStatus))
                return null;

            $supplierResponse = $this->supplierHandler->getResponse(
                sku: $item->sku,
                orderItemPublicId: $item->public_id,
            );

            if ($this->returnSuccessResult($supplierResponse, $item, $oldOrderItemStatus))
                return $supplierResponse;

            if ($this->returnOutOfStockResult($supplierResponse, $item, $oldOrderItemStatus))
                return $supplierResponse;
            
            $this->handleFailResponse($supplierResponse, $item, $oldOrderItemStatus);

            return $supplierResponse;
        });
    }

    protected function returnResultBeforeSupplierResponse(
        OrderItem $item, 
        PaymentStatus $paymentStatus,
        OrderStatus $oldOrderItemStatus
    ): bool
    {
        if ($item->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Paid) {
            $item = $this->orderItemRepository->updateStatus($item, OrderStatus::Paid);
            $this->logService->logOrderItemStatusUpdate($item, $oldOrderItemStatus, OrderStatus::Paid);
            $oldOrderItemStatus = $item->status;
        } elseif ($item->status == OrderStatus::Created && $paymentStatus == PaymentStatus::Failed) {
            $this->orderItemRepository->updateStatus($item, OrderStatus::PaymentFailed);
            $this->logService->logOrderItemStatusUpdate($item, $oldOrderItemStatus, OrderStatus::PaymentFailed);

            return true;
        } 

        if ($item->code) {
            if ($item->status != OrderStatus::Delivered) {
                $this->orderItemRepository->updateStatus($item, OrderStatus::Delivered);
                $this->logService->logOrderItemStatusUpdate($item, $oldOrderItemStatus, OrderStatus::Delivered);
            }

            return true;
        }

        if ($item->status != OrderStatus::Delivering) {
            $item = $this->orderItemRepository->updateStatus($item, OrderStatus::Delivering);
            $this->logService->logOrderItemStatusUpdate($item, $oldOrderItemStatus, OrderStatus::Delivering);
            $oldOrderItemStatus = $item->status;
        }       
        
        return false;
    }

    protected function returnSuccessResult(
        SupplierClientResponseDTO $supplierResponse, 
        OrderItem $item,
        OrderStatus $oldOrderItemStatus,
    ): bool
    {
        if ($supplierResponse->status != SupplierStatus::Ok->value) 
            return false;

        $this->logger->info("Key received", [
            'order_item_public_id' => $item->public_id,
            'order_public_id' => $item->orderPublicId,
            'code' => $supplierResponse->code,
        ]);

        try {
            $this->orderItemRepository->update($item, [
                'status' => OrderStatus::Delivered,
                'code' => $supplierResponse->code,
                'request_id' => $supplierResponse->requestId,
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == '23505' || str_contains($e->getMessage(), 'unique constraint')) {
                $this->orderItemRepository->updateStatus($item, OrderStatus::DeliveryFailed); 
                $this->logService->logOrderItemStatusUpdate($item, $oldOrderItemStatus, OrderStatus::DeliveryFailed);

                $this->logger->warning('Delivery failed', [
                    'order_item_public_id' => $item->public_id,
                    'order_public_id' => $item->orderPublicId,
                    'reason' => $supplierResponse->reason,
                ]);     
                
                $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
                    orderId: $item->order_id,
                    orderItemId: $item->id,
                    type: MoneyMoveType::Refund,
                    amount: $item->amount,
                ));     
                
                return true;
            }

            throw $e;
        }

        $this->logService->logOrderItemStatusUpdate($item, $oldOrderItemStatus, OrderStatus::Delivered);

        $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
            orderId: $item->order_id,
            orderItemId: $item->id,
            type: MoneyMoveType::Issued,
            amount: $item->amount,
        ));

        return true;
    }

    protected function returnOutOfStockResult(
        SupplierClientResponseDTO $supplierResponse,
        OrderItem $item,
        OrderStatus $oldOrderItemStatus,
    ): bool
    {

        if ($supplierResponse->reason != SupplierReason::OutOfStock->value)
            return false;

        $this->logger->warning('Out of stock', [
            'order_item_public_id' => $item->public_id,
            'order_public_id' => $item->orderPublicId,
            'sku' => $item->sku,
        ]);
        $this->orderItemRepository->updateStatus($item, OrderStatus::OutOfStock);
        $this->logService->logOrderItemStatusUpdate($item, $oldOrderItemStatus, OrderStatus::OutOfStock);

        $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
            orderId: $item->order_id,
            orderItemId: $item->id,
            type: MoneyMoveType::Refund,
            amount: $item->amount,
        ));

        return true;
    }

    protected function handleFailResponse(
        SupplierClientResponseDTO $supplierResponse,
        OrderItem $item,
        OrderStatus $oldOrderItemStatus,
    ): bool
    {
        if ($supplierResponse->reason == SupplierReason::RateLimited->value) {
            $this->logger->warning('Supplier rate limited', [
                'order_item_public_id' => $item->public_id,
                'order_public_id' => $item->orderPublicId,
                'reason' => $supplierResponse->reason,
            ]);                 

            return true;
        }

        $this->orderItemRepository->updateStatus($item, OrderStatus::DeliveryFailed); 
        $this->logService->logOrderItemStatusUpdate($item, $oldOrderItemStatus, OrderStatus::DeliveryFailed);
        $this->logger->error('Delivery failed', [
            'order_item_public_id' => $item->public_id,
            'order_public_id' => $item->orderPublicId,
            'reason' => $supplierResponse->reason,
        ]);               

        $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
            orderId: $item->order_id,
            orderItemId: $item->id,
            type: MoneyMoveType::Refund,
            amount: $item->amount,
        ));

        return true;
    }  
}