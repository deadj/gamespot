<?php

namespace App\Domain\Supplier\DTO;

class SupplierClientRequestDTO
{
    public function __construct(
        public string $requestId,
        public string $sku,
        public string $orderPublicId,
    ) {}
}