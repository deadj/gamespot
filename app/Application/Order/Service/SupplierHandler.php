<?php

namespace App\Application\Order\Service;

use App\Domain\Supplier\DTO\SupplierClientRequestDTO;
use App\Domain\Supplier\DTO\SupplierClientResponseDTO;
use App\Domain\Supplier\Enum\SupplierReason;
use App\Domain\Supplier\Enum\SupplierStatus;
use App\Domain\Supplier\Interface\SupplierInterface;
use Illuminate\Http\Client\ConnectionException;

class SupplierHandler
{   
    protected const int MAX_TRY = 3;
    protected const int BACKOFF_TIME = 500;

    public function __construct(
        protected SupplierInterface $supplierA,
        protected SupplierInterface $supplierB,
    ) {}    

    public function getResponse(string $sku, string $orderPublicId): SupplierClientResponseDTO
    {
        $requestDTO = new SupplierClientRequestDTO(
            requestId: "request_{$orderPublicId}_supplier_A",
            sku: $sku,
            orderPublicId: $orderPublicId,
        );

        $response = $this->makeRequest($this->supplierA, $requestDTO);

        if ($response->status == SupplierStatus::Ok->value || $response->reason == SupplierReason::AllTimeouts->value) 
            return $response;
        
        $requestDTO->requestId = "request_{$orderPublicId}_supplier_B";
        $response = $this->makeRequest($this->supplierB, $requestDTO);

        return $response;
    }

    protected function makeRequest(
        SupplierInterface $supplier, 
        SupplierClientRequestDTO $requestDTO
    ): SupplierClientResponseDTO
    {   
        try {
            return retry(
                self::MAX_TRY,
                function () use ($supplier, $requestDTO) {
                    $response = $supplier->getKey($requestDTO);
                    return $response;
                },
                fn (int $tryCount) => $tryCount * self::BACKOFF_TIME,
                fn (\Throwable $e) => $e instanceof ConnectionException,
            ); 
        } catch (ConnectionException $e) {
            return new SupplierClientResponseDTO(
                status: SupplierStatus::Error->value,
                reason: SupplierReason::AllTimeouts->value,
            );
        }  
    }
}