<?php

namespace App\Domain\Supplier\DTO;

readonly class SupplierClientRequestDTO
{
    public function __construct(
        public string $requestId,
        public string $sku,
        public string $orderPublicId,
    ) {}
}