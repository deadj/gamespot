<?php

namespace App\Jobs;

use App\Application\Order\UseCase\DeliverOrderItemUseCase;
use App\Application\Order\UseCase\HandleOrderResultUseCase;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Supplier\Enum\SupplierReason;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeliverOrderItemJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        protected int $orderId,
        protected int $orderItemId,
        protected PaymentStatus $paymentStatus,
    ){}

    public function handle(
        DeliverOrderItemUseCase $deliverOrderItemUseCase,
        HandleOrderResultUseCase $handleOrderResultUseCase,
    ): void
    {
        $response = $deliverOrderItemUseCase->execute($this->orderItemId, $this->paymentStatus);

        if ($response === null)
            return;

        if ($response?->reason == SupplierReason::RateLimited->value) {
            $this->release(60);
            return;
        }

        $handleOrderResultUseCase->execute($this->orderId);
    }
}
