<?php

namespace App\Domain\Payment\Enum;

enum PaymentStatus: string
{
    case Paid = 'paid';
    case Failed = 'failed';
}