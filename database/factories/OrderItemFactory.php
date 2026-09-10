<?php

namespace Database\Factories;

use App\Domain\Order\Enum\OrderStatus;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Models\OrderItem;
use App\Infrastructure\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();
        $order = Order::factory()->create();

        return [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'public_id' => 'ord_item_' . bin2hex(random_bytes(16)),
            'sku' => $product->sku,
            'status' => OrderStatus::Created,
            'amount' => $product->price,
            'currency' => 'RUB',
            'code' => 'TEST_CODE_' . rand(1, 100000000),
        ];
    }      
}


