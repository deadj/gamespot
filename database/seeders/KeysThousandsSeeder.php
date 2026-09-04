<?php

namespace Database\Seeders;

use App\Infrastructure\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KeysThousandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skus = Product::pluck('sku')->toArray();

        if (empty($skus))
            return;        

        $keys = [];
        $keysCount = 10000;
        $insertSize = 500;

        for ($i = 0; $i < $keysCount; $i++) {
            $keys[] = [
                'sku' => $skus[array_rand($skus)],
                'code' => $this->generateUniqueCode($i),
            ];

            if (count($keys) >= $insertSize) {
                DB::table('keys')->insert($keys);
                $keys = [];
            }
        }

        if (!empty($keys)) 
            DB::table('keys')->insert($keys);
    }

    protected function generateUniqueCode(int $index): string
    {
        $part1 = strtoupper(substr(md5("{$index}_part1"), 0, 4));
        $part2 = strtoupper(substr(md5("{$index}_part2"), 0, 4));
        $part3 = strtoupper(substr(md5("{$index}_part3"), 0, 4));
        
        return "{$part1}-{$part2}-{$part3}";
    }    
}
