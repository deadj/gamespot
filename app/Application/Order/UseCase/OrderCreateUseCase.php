<?php

namespace App\Application\Order\UseCase;

use App\Domain\Order\DTO\OrderCreateDTO;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Shared\LoggerInterface;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Models\Product;

class OrderCreateUseCase
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected LoggerInterface $logger,
    ) {}

    public function execute(string $sku): Order
    {
        $product = $this->productRepository->getBySku($sku);

        if (!$product) {
            $this->logger->warning("Product not fount", ['sku' => $sku]);
            throw new ProductNotFoundException("product $sku not found");
        }

        $orderCreateDTO = new OrderCreateDTO(
            publicId: $this->getPublicId($sku, $product),
            sku: $sku,
            amount: $product->price,
            currency: $product->currency,
            status: OrderStatus::Created,
        );
        
        $order = $this->orderRepository->create($orderCreateDTO);
        $this->logger->info("Order created", [
            'sku' => $sku,
            'order_public_id' => $order->public_id,
        ]);
        return $order;
    }

    protected function getPublicId(string $sku, Product $product): string
    {
        $publicId = $sku . $product->price . $product->currency . bin2hex(random_bytes(8));
        $publicId = "ord_" . md5($publicId);
        return $publicId;
    }
}