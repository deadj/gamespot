<?php

namespace App\Application\Order\Service;

use App\Domain\Order\Repository\OrderItemRepositoryInterface;
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
    protected const int MAX_TRY_FOR_CODE_ERROR = 3;
    protected const int BACKOFF_TIME = 500;

    public function __construct(
        protected SupplierInterface $supplierA,
        protected SupplierInterface $supplierB,
        protected LoggerInterface $logger,
        protected OrderItemRepositoryInterface $orderItemRepository,
    ) {}    

    public function getResponse(string $sku, string $orderItemPublicId): SupplierClientResponseDTO
    {
        $suppliers = [
            $this->supplierA,
            $this->supplierB,
        ];

        $supplierLastKey = array_key_last($suppliers);

        foreach ($suppliers as $key => $currentSupplier) {
            for ($try = 1; $try <= self::MAX_TRY_FOR_CODE_ERROR; $try++) {
                $request = new SupplierClientRequestDTO(
                    requestId: "request_{$orderItemPublicId}_supplier_{$currentSupplier->name}_time_{$try}",
                    sku: $sku,
                    orderItemPublicId: $orderItemPublicId,
                );

                $response = $this->makeRequest($currentSupplier, $request);

                if ($response->reason == SupplierReason::AllTimeouts->value) {
                    $this->logSupplierResponse($request, $response, $currentSupplier->name, false);
                    return $response;
                }                

                if ($response->status == SupplierStatus::Ok->value) {
                    $item = $this->orderItemRepository->getByCode($response->code);

                    if (!$item || $item->public_id == $orderItemPublicId) {
                        $this->logSupplierResponse($request, $response, $currentSupplier->name, true);
                        return $response;                
                    }

                    $this->logger->warning("Duplicate code from supplier", [
                        'supplier' => $currentSupplier->name,
                        'request_id' => $request->requestId,
                        'code' => $response->code,
                    ]);
                }
            }      
            
            $this->logSupplierResponse($request, $response, $currentSupplier->name, false);   
            
            if ($key !=  $supplierLastKey) {
                $this->logger->info(
                    "Supplier {$currentSupplier->name}  fail. Supplier {$suppliers[$key + 1]->name} start", 
                    [
                        'order_public_id' => $orderItemPublicId,
                        'provider_a_reason' => $response->reason,
                    ],
                );     
            }            
        }

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
        bool $success = true,
    ): void
    {
        $logData = [
            'order_item_public_id' => $request->orderItemPublicId,
            'request_id' => $request->requestId,
            'supplier' => $supplier,
        ];

        if ($response->status == SupplierStatus::Ok->value && $success) {
            $logData['code'] = $response->code;
            $this->logger->info("Supplier {$supplier} success", $logData);
            return;
        }

        if ($response->status == SupplierStatus::Ok->value && !$success) {
            $logData['code'] = $response->code;
            $this->logger->warning("Supplier {$supplier} warning", $logData);
            return;
        }

        $logData['reason'] = $response->reason;
        $this->logger->error("All suppliers failed", $logData);
    }
}