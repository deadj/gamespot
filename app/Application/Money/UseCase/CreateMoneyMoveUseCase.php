<?php

namespace App\Application\Money\UseCase;

use App\Domain\Money\DTO\MoneyMoveDTO;
use App\Domain\Money\Repository\MoneyMoveRepositoryInterface;

class CreateMoneyMoveUseCase
{
    public function __construct(
        protected MoneyMoveRepositoryInterface $moneyMoveRepository,
    ) {}

    public function execute(MoneyMoveDTO $dto): void
    {
        $this->moneyMoveRepository->create($dto);
    }
}