<?php

namespace Database\Factories;

use App\Domain\Order\Enum\OrderStatus;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;


class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'public_id' => 'ord_' . bin2hex(random_bytes(16)),
            'status' => OrderStatus::Created,
        ];
    }
    
    public function withItems(int $count): static
    {
        return $this->afterCreating(function (Order $order) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'code' => NULL,
                ]);
            }
        });
    }
}
