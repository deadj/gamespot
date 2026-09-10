<?php

namespace App\Application\Money\UseCase;

use App\Domain\Money\DTO\MoneyMoveDTO;
use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Money\Repository\MoneyMoveRepositoryInterface;
use App\Domain\Shared\LoggerInterface;
use Illuminate\Database\QueryException;

class CreateMoneyMoveUseCase
{
    public function __construct(
        protected MoneyMoveRepositoryInterface $moneyMoveRepository,
        protected LoggerInterface $logger,
    ) {}

    public function execute(MoneyMoveDTO $dto): void
    {
        $existMoneyMove = $this->moneyMoveRepository->getByUniqueIndex(
            orderId: $dto->orderId,
            type: $dto->type,
            orderItemId: $dto->orderItemId, 
        );

        if ($existMoneyMove) {
            $this->logger->warning("Money move duplicate", $dto->toArray());
            return;
        }

        if ($dto->type == MoneyMoveType::Issued) {
            $refundExists = $this->moneyMoveRepository->getByUniqueIndex(
                orderId: $dto->orderId, 
                orderItemId: $dto->orderItemId,
                type: MoneyMoveType::Refund
            );

            if ($refundExists) 
                $this->moneyMoveRepository->create(new MoneyMoveDTO(
                    orderId: $dto->orderId,
                    orderItemId: $dto->orderItemId,
                    type: MoneyMoveType::RefundCancelled,
                    amount: $refundExists->amount,
                ));
        }
        
        try {
            $this->moneyMoveRepository->create($dto);
        } catch (QueryException $e) {
            if ($e->getCode() == '23505' || str_contains($e->getMessage(), 'unique constraint')) {
                $this->logger->warning("Money move duplicate", $dto->toArray());
                return;
            }
            
            throw $e;
        }
    }
}