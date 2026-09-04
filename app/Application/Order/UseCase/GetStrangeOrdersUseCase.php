<?php

namespace App\Application\Order\UseCase;

use App\Domain\Order\Repository\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class GetStrangeOrdersUseCase
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
    ) {}

    public function execute(): Collection
    {
        $orders = $this->orderRepository->getStrangeOrders();
        return $orders;
    }
}