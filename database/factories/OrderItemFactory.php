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
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::inRandomOrder()->first()->id,
            'public_id' => function (array $attributes) {
                return 'ord_item_' . $attributes['order_id'] .  bin2hex(random_bytes(16));
            },            
            'sku' => function (array $attributes) {
                return Product::find($attributes['product_id'])->sku;
            },
            'amount' => function (array $attributes) {
                return Product::find($attributes['product_id'])->price;
            },         
            'status' => OrderStatus::Created,
            'currency' => 'RUB',
            'code' => 'TEST_CODE_' . rand(1, 100000000),
        ];
    }      
}


