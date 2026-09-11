<?php

namespace App\Console\Commands;

use App\Domain\Payment\Enum\PaymentStatus;
use App\Infrastructure\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

#[Signature('test:rate-limiter')]
#[Description('test for rate limiter')]
class RateLimiterTestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        Redis::del('orders:in_queue');

        $ordersCount = 30;

        for ($i = 0; $i < $ordersCount; $i++) {
            $order = Order::factory()->withItems(1)->create();
            $response = Http::post(
                route('webhook.payment'), 
                $this->getRequestData($order)
            );
        }
    }

    protected function getRequestData(Order $order): array
    {
        $request = [
            'event_id' => "event_test_{$order->id}",
            'order_id' => $order->public_id,
            'status' => PaymentStatus::Paid->value,
            'amount' => $order->items->sum('amount'),
            'currency' => 'RUB',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
        
        return $request;
    }        
}
