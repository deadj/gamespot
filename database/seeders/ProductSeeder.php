<?php

namespace Database\Seeders;

use App\Infrastructure\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'STEAM-TOPUP-500',
                'name' => 'Пополнение Steam 500 ₽',
                'type' => 'topup',
                'price' => 500.00,
                'currency' => 'RUB',
                'image' => 'assets/steam.png',
            ],
            [
                'sku' => 'STEAM-TOPUP-1000',
                'name' => 'Пополнение Steam 1000 ₽',
                'type' => 'topup',
                'price' => 1000.00,
                'currency' => 'RUB',
                'image' => 'assets/steam.png',
            ],
            [
                'sku' => 'STEAM-TOPUP-2500',
                'name' => 'Пополнение Steam 2500 ₽',
                'type' => 'topup',
                'price' => 2500.00,
                'currency' => 'RUB',
                'image' => 'assets/steam.png',
            ],
            [
                'sku' => 'KEY-CS2-PRIME',
                'name' => 'CS2 Prime Status ключ',
                'type' => 'key',
                'price' => 1290.00,
                'currency' => 'RUB',
                'image' => 'assets/cs2.png',
            ],
            [
                'sku' => 'KEY-GTA5',
                'name' => 'GTA V ключ активации',
                'type' => 'key',
                'price' => 1990.00,
                'currency' => 'RUB',
                'image' => 'assets/gta5.png',
            ],
            [
                'sku' => 'KEY-EFT',
                'name' => 'Escape from Tarkov ключ',
                'type' => 'key',
                'price' => 3490.00,
                'currency' => 'RUB',
                'image' => 'assets/eft.png',
            ],
            [
                'sku' => 'SUB-DISCORD-1M',
                'name' => 'Discord Nitro 1 месяц',
                'type' => 'subscription',
                'price' => 399.00,
                'currency' => 'RUB',
                'image' => 'assets/discord.png',
            ],
            [
                'sku' => 'SUB-YT-3M',
                'name' => 'YouTube Premium 3 месяца',
                'type' => 'subscription',
                'price' => 1490.00,
                'currency' => 'RUB',
                'image' => 'assets/youtube.png',
            ],
            [
                'sku' => 'SUB-SPOTIFY-1M',
                'name' => 'Spotify Premium 1 месяц',
                'type' => 'subscription',
                'price' => 299.00,
                'currency' => 'RUB',
                'image' => 'assets/spotify.png',
            ],
            [
                'sku' => 'GIFT-PSN-1000',
                'name' => 'PlayStation Store карта 1000 ₽',
                'type' => 'giftcard',
                'price' => 1000.00,
                'currency' => 'RUB',
                'image' => 'assets/psn.png',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
