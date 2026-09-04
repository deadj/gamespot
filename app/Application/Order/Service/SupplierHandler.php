<?php

namespace App\Application\Order\Service;

use App\Domain\Shared\LoggerInterface;
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
        protected LoggerInterface $logger,
    ) {}    

    public function getResponse(string $sku, string $orderPublicId): SupplierClientResponseDTO
    {
        $request = new SupplierClientRequestDTO(
            requestId: "request_{$orderPublicId}_supplier_A",
            sku: $sku,
            orderPublicId: $orderPublicId,
        );

        $response = $this->makeRequest($this->supplierA, $request);

        if (
            $response->status == SupplierStatus::Ok->value
            || $response->reason == SupplierReason::AllTimeouts->value
        ) {
            $this->logSupplierResponse($request, $response, 'A');
            return $response;
        }

        $this->logger->info('Supplier A fail. Supplier B start', [
            'order_public_id' => $orderPublicId,
            'provider_a_reason' => $response->reason,
        ]);

        $request->requestId = "request_{$orderPublicId}_supplier_B";
        $response = $this->makeRequest($this->supplierB, $request);

        $this->logSupplierResponse($request, $response, 'B');

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

    protected function logSupplierResponse(
        SupplierClientRequestDTO $request,
        SupplierClientResponseDTO $response, 
        string $supplier,
    ): void
    {
        $logData = [
            'order_public_id' => $request->orderPublicId,
            'request_id' => $request->requestId,
            'supplier' => $supplier,
        ];


        if ($response->status == SupplierStatus::Ok->value) {
            $logData['code'] = $response->code;
            $this->logger->info('Key received', $logData);
            return;
        }
        
        $logData['reason'] = $response->reason;
        $this->logger->error("All suppliers failed", $logData);
    }
}