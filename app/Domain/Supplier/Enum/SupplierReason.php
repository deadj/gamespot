<?php

namespace App\Domain\Supplier\Enum;

enum SupplierReason: string
{
    case Error = 'error';
    case Timeout = 'timeout';
    case AllTimeouts = 'all_timeounts';
    case OutOfStock = 'out_of_stock';
}