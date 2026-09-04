<?php

namespace App\Infrastructure\Supplier;

use App\Domain\Supplier\Repository\KeyRepositoryInterface;
use App\Domain\Supplier\DTO\SupplierClientRequestDTO;
use App\Domain\Supplier\DTO\SupplierClientResponseDTO;
use App\Domain\Supplier\Enum\SupplierReason;
use App\Domain\Supplier\Enum\SupplierStatus;
use App\Domain\Supplier\Interface\SupplierInterface;
use Illuminate\Http\Client\ConnectionException;
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
        if ($key = $this->keyRepository->getByRequestId($dto->requestId)) {
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Ok->value,
                requestId: $dto->requestId,
                code: $key->code,
            );
        }
        
        if (rand(1, 100) <= $this->errorPercent) 
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Error->value,
                reason: SupplierReason::Error->value,
            );

        $key = $this->keyRepository->markForOrder($dto->sku, $dto->requestId);

        if (rand(1, 100) <= $this->timeoutPercent) 
            throw new ConnectionException('Simulated supplier timeout');     

        if ($key) {
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Ok->value,
                requestId: $dto->requestId,
                code: $key->code,
            );
        }

        return new SupplierClientResponseDTO(
            status: SupplierStatus::Error->value,
            reason: SupplierReason::OutOfStock->value
        );
    }
}