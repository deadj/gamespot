<?php

use App\Domain\Order\Enum\OrderStatus;

class OrderChangeStatusException extends Exception
{
    public function __construct(int $orderId, OrderStatus $from, OrderStatus $to)
    {   
        parent::__construct(
            "Order {$orderId}: status change fail. from: {$from->value} to: {$to->value}."
        );        
    }
}