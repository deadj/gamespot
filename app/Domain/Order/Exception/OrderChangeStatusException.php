<?php

namespace App\Domain\Order\Exception;

use App\Domain\Order\Enum\OrderStatus;
use Exception;

class OrderChangeStatusException extends Exception
{
    public function __construct(int $orderId, OrderStatus $from, OrderStatus $to)
    {   
        parent::__construct(
            "Order {$orderId}: status change fail. from: {$from->value} to: {$to->value}."
        );        
    }
}