<?php

namespace App\Application\Payment\UseCase;

use App\Application\Money\UseCase\CreateMoneyMoveUseCase;
use App\Application\Order\UseCase\DeliverOrderUseCase;
use App\Application\Payment\DTO\PaymentDTO;
use App\Domain\Money\DTO\MoneyMoveDTO;
use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Order\Exception\OrderPartiallyDeliveredException;
use App\Domain\Order\Repository\OrderItemRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Domain\Payment\Exception\PaymentAlreadyProcessedException;
use App\Domain\Payment\Repository\PaymentLogRepositoryInterface;
use App\Domain\Payment\Repository\PaymentRepositoryInterface;
use App\Domain\Shared\LoggerInterface;
use Illuminate\Database\QueryException;

class HandlePaymentWebhookUseCase
{
    public function __construct(
        protected PaymentRepositoryInterface $paymentRepository,
        protected PaymentLogRepositoryInterface $paymentLogRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected DeliverOrderUseCase $deliverOrderUseCase,
        protected CreateMoneyMoveUseCase $createMoneyMoveUseCase,
        protected LoggerInterface $logger,
    ) {}

    public function execute(PaymentDTO $dto): void
    {
        $this->logger->info("New Payment request", $dto->toArray());
        $this->paymentLogRepository->create($dto);

        try {
            if ($this->paymentRepository->getByEventId($dto->eventId)) {
                $this->logger->info("Payment already processed", ['event_id' => $dto->eventId]);
                throw new PaymentAlreadyProcessedException("payment {$dto->eventId} already processed");
            }

            $order = $this->orderRepository->getByPublicId($dto->orderPublicId);

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
            
            // TODO: не очевидно, что делать в ситуации, когда у продуктов разная валюта
            // if (
            //     bccomp((string) $order->amount, (string) $dto->amount, 2) !== 0
            //     || $order->currency !== $dto->currency
            // ) {
            //     $this->logger->error("Payment Amount/currency error", ['event_id' => $dto->eventId]);
            //     throw new PaymentAmountException('Amount/currency error');
            // }

            if (in_array($order->status, [
                OrderStatus::Delivered,
                OrderStatus::PaymentFailed,
                OrderStatus::PartiallyDelivered,
            ])) {
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

            $order = $this->deliverOrderUseCase->execute($order, $dto->status);

            if ($order->status == OrderStatus::PartiallyDelivered) {
                $notDelivetedItemPublicIds = $this->orderItemRepository
                    ->getNotDelivetedByOrderId($order->id)
                    ->pluck('public_id')
                    ->toArray();

                $notDelivetedItemPublicIdsText = implode(', ', $notDelivetedItemPublicIds);

                $this->logger->warning('Order is partially delivered', [
                    'order_public_id' => $order->public_id,
                    'not_deliveted_items' => $notDelivetedItemPublicIds,
                ]);

                throw new OrderPartiallyDeliveredException(
                    'Order is partially delivered. Not deliveted items: ' . $notDelivetedItemPublicIdsText
                ); 
            }            
        } catch (QueryException $e) {
            if ($e->getCode() == '23505' || str_contains($e->getMessage(), 'unique constraint')) {
                $this->logger->info("Payment duplicate", ['event_id' => $dto->eventId]);
                throw new PaymentAlreadyProcessedException("payment {$dto->eventId} already processed");
            }
            
            throw $e;
        }
    }
}