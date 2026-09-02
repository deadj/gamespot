<?php

namespace App\Application\Order\UseCase;

use App\Domain\Order\Exception\OrderNotFoundException;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Infrastructure\Models\Order;

class GetOrderUseCase
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
    ) {}

    public function execute(int $orderId): Order
    {
        $order = $this->orderRepository->getById($orderId);

        if (!$order)
            throw new OrderNotFoundException("order $orderId not found");

        return $order;
    }
}