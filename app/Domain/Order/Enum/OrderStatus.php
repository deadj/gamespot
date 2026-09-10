<?php

namespace App\Domain\Order\Enum;

enum OrderStatus: string
{
    case Created = 'created';
    case Paid = 'paid';
    case Delivering = 'delivering';
    case Delivered = 'delivered';
    case PartiallyDelivered = 'partially_delivered';
    case PaymentFailed = 'payment_failed';
    case OutOfStock = 'out_of_stock';
    case DeliveryFailed = 'delivery_failed';
    case Refunded = 'refunded';

    public function getChangePermission(self $neededStatus): bool
    {
        return in_array($neededStatus, $this->getAllowedChanges());
    }

    private function getAllowedChanges(): array
    {
        return match ($this) {
            self::Created => [
                self::Paid, 
                self::PaymentFailed,
            ],
            self::Paid => [
                self::Delivering,
            ],
            self::Delivering => [
                self::Delivered, 
                self::OutOfStock, 
                self::DeliveryFailed,
                self::PartiallyDelivered,
            ],
            self::OutOfStock, 
            self::DeliveryFailed => [
                self::Delivering,
                self::Refunded,
                self::PartiallyDelivered,
            ],
            self::Delivered, 
            self::PartiallyDelivered => [
                self::Delivering,
            ],
            self::PaymentFailed => [],
        };
    }    
}