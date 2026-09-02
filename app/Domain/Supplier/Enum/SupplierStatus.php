<?php

namespace App\Domain\Supplier\Enum;

enum SupplierStatus: string
{
    case Ok = 'ok';
    case Error = 'error';
}