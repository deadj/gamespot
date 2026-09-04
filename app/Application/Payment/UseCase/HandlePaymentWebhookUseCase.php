<?php

namespace App\Application\Payment\UseCase;

use App\Application\Money\UseCase\CreateMoneyMoveUseCase;
use App\Application\Order\UseCase\DeliverOrderUseCase;
use App\Application\Payment\DTO\PaymentDTO;
use App\Domain\Money\DTO\MoneyMoveDTO;
use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Payment\Exception\PaymentAlreadyProcessedException;
use App\Domain\Payment\Exception\PaymentAmountException;
use App\Domain\Payment\Repository\PaymentLogRepositoryInterface;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\Shared\LoggerInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class HandlePaymentWebhookUseCase
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected PaymentLogRepositoryInterface $paymentLogRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected DeliverOrderUseCase $deliverOrderUseCase,
        protected CreateMoneyMoveUseCase $createMoneyMoveUseCase,
        protected LoggerInterface $logger,
    ) {}

    public function execute(PaymentDTO $dto): void
    {
        $this->logger->info("New Payment request", $dto->toArray());
        $this->paymentLogRepository->create($dto);

        try {
            DB::transaction(function () use ($dto) {
                if ($this->paymentRepository->getByEventId($dto->eventId)) {
                    $this->logger->info("Payment already processed", ['event_id' => $dto->eventId]);
                    throw new PaymentAlreadyProcessedException("payment {$dto->eventId} already processed");
                }

                $order = $this->orderRepository->getByPublicIdForUpdate($dto->orderPublicId);

                if (!$order) {
                    $this->logger->warning('Order not found', [
                        'event_id' => $dto->eventId,
                        'order_public_id' => $dto->orderPublicId,
                    ]);
                    throw new OrderNotFoundException("Order {$dto->orderPublicId} not found");
                }

                if ($order->status == OrderStatus::Paid) {
                    $this->logger->info('Order already paid', ['order_public_id' => $dto->orderPublicId]);
                    throw new PaymentAlreadyProcessedException("payment {$dto->eventId} already processed");
                }

                if (
                    bccomp((string) $order->amount, (string) $dto->amount, 2) !== 0
                    || $order->currency !== $dto->currency
                ) {
                    $this->logger->error("Payment Amount/currency error", ['event_id' => $dto->eventId]);
                    throw new PaymentAmountException('Amount/currency error');
                }

                if ($order->status == OrderStatus::Delivered || $order->status == OrderStatus::PaymentFailed) {
                    $this->logger->info("Order already finished", [
                        'event_id' => $dto->eventId,
                        'order_public_id' => $dto->orderPublicId,
                        'status' => $order->status->value,
                    ]);
                    return;
                }

                $payment = $this->paymentRepository->create($dto);

                if ($payment->status == PaymentStatus::Paid)
                    $this->createMoneyMoveUseCase->execute(new MoneyMoveDTO(
                        orderId: $order->id,
                        type: MoneyMoveType::Received,
                        amount: $payment->amount
                    ));

                $this->deliverOrderUseCase->execute($order, $dto->status);
            });
        } catch (QueryException $e) {
            if ($e->getCode() == '23505' || str_contains($e->getMessage(), 'unique constraint')) {
                $this->logger->info("Payment duplicate", ['event_id' => $dto->eventId]);
                throw new PaymentAlreadyProcessedException("payment {$dto->eventId} already processed");
            }
            
            throw $e;
        }
    }
}