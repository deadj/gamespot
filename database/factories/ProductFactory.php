<?php

namespace Database\Factories;

use App\Domain\Product\Enum\ProductType;
use App\Infrastructure\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'TEST_PRODUCT_' . rand(1, 10000000),
            'name' => fake()->text(rand(10, 30)),
            'type' => Arr::random(ProductType::cases()),
            'price' => rand(100, 10000),
            'currency' => 'RUB',
        ];
    }
}
