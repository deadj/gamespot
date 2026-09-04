<?php

namespace App\Domain\Money\Enum;

enum MoneyMoveType: string
{
    case Received = 'received';
    case Issued = 'issued';
}