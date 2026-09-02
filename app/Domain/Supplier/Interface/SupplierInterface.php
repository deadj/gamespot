<?php

namespace App\Domain\Supplier\Interface;

use App\Domain\Supplier\DTO\SupplierClientRequestDTO;
use App\Domain\Supplier\DTO\SupplierClientResponseDTO;

interface SupplierInterface
{
    public function getKey(SupplierClientRequestDTO $dto): SupplierClientResponseDTO;
}