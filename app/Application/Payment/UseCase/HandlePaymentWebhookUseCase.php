<?php

namespace App\Application\Payment\UseCase;

use App\Application\Order\UseCase\DeliverOrderUseCase;
use App\Application\Payment\DTO\PaymentDTO;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Exception\PaymentAlreadyProcessedException;
use App\Domain\Payment\Exception\PaymentAmountException;
use App\Domain\Payment\Repository\PaymentLogRepositoryInterface;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use Illuminate\Support\Facades\DB;

class HandlePaymentWebhookUseCase
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected PaymentLogRepositoryInterface $paymentLogRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected DeliverOrderUseCase $deliverOrderUseCase,
    ) {}

    public function execute(PaymentDTO $dto): void
    {
        $this->paymentLogRepository->create($dto);

        DB::transaction(function () use ($dto) {
            if ($this->paymentRepository->getByEventId($dto->eventId))
               throw new PaymentAlreadyProcessedException("payment {$dto->eventId} already processed");

            $order = $this->orderRepository->getByPublicIdForUpdate($dto->orderPublicId);

            if (!$order)
                throw new OrderNotFoundException("Order {$dto->orderPublicId} not found");

            if ($order->status == OrderStatus::Paid)
                throw new PaymentAlreadyProcessedException("payment {$dto->eventId} already processed");

            if (bccomp((string) $order->amount, (string) $dto->amount, 2) !== 0 || $order->currency !== $dto->currency)
                throw new PaymentAmountException('Amount/currency error');

            if ($order->status == OrderStatus::Delivered || $order->status == OrderStatus::PaymentFailed)
                return;

            $this->paymentRepository->create($dto);
            $this->deliverOrderUseCase->execute($order, $dto->status);
        });
    }
}