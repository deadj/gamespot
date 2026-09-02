<?php

namespace App\Domain\Supplier\DTO;

readonly class SupplierClientResponseDTO
{
    public function __construct(
        public string $status,
        public ?string $requestId = null,
        public ?string $code = null,
        public ?string $reason = null,
    ) {}
}