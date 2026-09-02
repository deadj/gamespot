<?php

namespace App\Domain\Product\Enum;

enum ProductType: string
{
    case Topup = 'topup';
    case Key = 'key';
    case Subscription = 'subscription';
    case Giftcard = 'giftcard';
}