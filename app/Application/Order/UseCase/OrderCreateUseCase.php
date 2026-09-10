<?php

namespace App\Application\Order\UseCase;

use App\Domain\Order\DTO\OrderCreateDTO;
use App\Domain\Order\DTO\OrderItemDTO;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Repository\OrderItemRepositoryInterface;
use App\Domain\Order\Repository\OrderRepositoryInterface;
use App\Domain\Product\Exception\AllProductsNotFoundException;
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
        protected OrderItemRepositoryInterface $orderItemRepository,
        protected LoggerInterface $logger,
    ) {}

    public function execute(array $skus): Order
    {
        $notFoundSkus = [];
        $fountProducts = [];

        foreach ($skus as $sku) {
            $product = $this->productRepository->getBySku($sku);

            if ($product) {
                $fountProducts[] = $product;
            } else {
                $this->logger->warning("Product not fount", ['sku' => $sku]);
                $notFoundSkus[] = $sku;
            }
        }

        $notFoundSkusText = implode(', ', $notFoundSkus);

        if (count($skus) === count($notFoundSkus)) {

            $this->logger->warning("Products not fount", $notFoundSkus);
            throw new AllProductsNotFoundException("products $notFoundSkusText not found");            
        }


        $orderCreateDTO = new OrderCreateDTO(
            publicId: $this->getOrderPublicId($fountProducts),
            status: OrderStatus::Created,
        );
        
        $order = $this->orderRepository->create($orderCreateDTO);
     
        $this->logger->info("Order created", [
            'skus' => $skus,
            'order_public_id' => $order->public_id,
        ]);

        foreach ($fountProducts as $product) {
            $orderItemCreateDTO = new OrderItemDTO(
                orderId: $order->id,
                productId: $product->id,
                amount: $product->price,
                currency: $product->currency,
                sku: $product->sku,
                publicId: $this->getItemPublicId($product),
                status: OrderStatus::Created,
            );

            $item = $this->orderItemRepository->create($orderItemCreateDTO);

            $this->logger->info("Order item created", [
                'sku' => $item->sku,
                'order_public_id' => $order->public_id,
                'order_item_public_id' => $item->public_id,
            ]);            
        }

        if (count($notFoundSkus) > 0) {
            $this->logger->warning("Products not fount", $notFoundSkus);
            throw new ProductNotFoundException("products $notFoundSkus not found");
        }


        $order->refresh();
        return $order;        
    }

    protected function getOrderPublicId(array $products): string
    {
        $textForHash = "";

        foreach ($products as $product) {
            $textForHash .= $product->sku . $product->price . $product->currency;
        }

        $publicId = $textForHash . bin2hex(random_bytes(8));
        $publicId = "ord_" . md5($publicId);

        return $publicId;
    }

    protected function getItemPublicId(Product $product): string
    {
        $publicId = $product->sku . $product->price . $product->currency . bin2hex(random_bytes(8));
        $publicId = "ord_item_" . md5($publicId);
        return $publicId;        
    }
}