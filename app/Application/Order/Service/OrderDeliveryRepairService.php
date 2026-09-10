<?php

namespace App\Application\Order\Service;

use App\Application\Order\UseCase\DeliverOrderUseCase;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Shared\LoggerInterface;
use Illuminate\Support\Facades\DB;

class OrderDeliveryRepairService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected DeliverOrderUseCase $deliverOrderUseCase,
        protected LoggerInterface $logger,
    ) {}

    public function handleNotDeliveredOrders(): void
    {
        $neededStatuses = [
            OrderStatus::Paid,
            OrderStatus::Delivering,
            OrderStatus::OutOfStock,
            OrderStatus::DeliveryFailed,
        ];

        $orderIds = $this->orderRepository->getByStatuses($neededStatuses)
            ->filter(fn ($order) => $order->updated_at->lt(now()->subMinutes(10)))
            ->pluck('id');

        foreach ($orderIds as $orderId) {
            DB::transaction(function () use ($orderId) {
                $order = $this->orderRepository->getByIdForUpdate($orderId);

                if ($order->payment?->status != PaymentStatus::Paid)
                    return;

                $this->logger->info('Order repairing', [
                    'order_public_id' => $order->public_id,
                    'status' => $order->status->value,
                ]);            

                $this->deliverOrderUseCase->execute($order, $order->payment->status);
            });
        }
    }
}