<?php

namespace App\Infrastructure\Supplier;

use App\Domain\Order\Repository\KeyRepositoryInterface;
use App\Domain\Supplier\DTO\SupplierClientRequestDTO;
use App\Domain\Supplier\DTO\SupplierClientResponseDTO;
use App\Domain\Supplier\Enum\SupplierReason;
use App\Domain\Supplier\Enum\SupplierStatus;
use App\Domain\Supplier\Interface\SupplierInterface;
use Override;

class SupplierClient implements SupplierInterface
{
    public function __construct(
        protected KeyRepositoryInterface $keyRepository,
        protected int $errorPercent = 0,
        protected int $timeoutPercent = 0,
    ) {}

    #[Override]
    public function getKey(SupplierClientRequestDTO $dto): SupplierClientResponseDTO
    {

        if (rand(0, 100) <= $this->timeoutPercent) 
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Error->value,
                reason: SupplierReason::Timeout->value,
            );
        
        if (rand(0, 100) <= $this->errorPercent) 
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Error->value,
                reason: SupplierReason::Error->value,
            );

        if ($key = $this->keyRepository->getByOrderId($dto->orderPublicId)) {
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Ok->value,
                requestId: $dto->requestId,
                code: $key->code,
            );
        }        

        if ($key = $this->keyRepository->markForOrder($dto->sku, $dto->orderPublicId)) {
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Ok->value,
                requestId: $dto->requestId,
                code: $key->code,
            );
        } else {
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Error->value,
                reason: SupplierReason::OutOfStock->value
            );
        }

    }
}