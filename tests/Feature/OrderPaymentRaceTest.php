<?php

namespace Tests\Feature;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Infrastructure\Models\Key;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Models\Payment;
use App\Infrastructure\Models\Product;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OrderPaymentRaceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->checkTestDB();

        DB::statement('TRUNCATE TABLE payment_logs, payments, orders RESTART IDENTITY CASCADE;');
        Key::query()->update(['order_id' => null]);
    }    

    protected function checkTestDB(): void
    {
        $database = DB::connection()->getDatabaseName();
        if (!str_contains($database, 'test') || app()->environment('production'))
            throw new RuntimeException();
            
    }

    public function test_race(): void
    {
        $testUrl = "http://127.0.0.1:8001";

        $product = Product::first();        
        $orderCreatingResponse = Http::post($testUrl . '/api/orders',  ['sku' => $product->sku]);
        $this->assertTrue($orderCreatingResponse->successful());
        $orderCreatingData = $orderCreatingResponse->json()['data'];
        $publicOrderId = $orderCreatingData['order_id'];

        $paymentWebhookRequestData = [
            'event_id' => 'event_test_12345',
            'order_id' => $publicOrderId,
            'status' => PaymentStatus::Paid->value,
            'amount' => $orderCreatingData['amount'],
            'currency' => $orderCreatingData['currency'],
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
        
        $testUrl = $testUrl . "/api/webhooks/payment";
        $poolResponses = Http::pool(function (Pool $pool) use ($testUrl, $paymentWebhookRequestData) {
            $requests = [];
            
            for ($i = 0; $i < 50; $i++) {
                $requests[] = $pool->post($testUrl, $paymentWebhookRequestData);
            }

            return $requests;
        });

        foreach ($poolResponses as $key => $response) {
            $this->assertTrue($response->successful(), "HTTP {$response->status()}: " . $response->body());
        }
        
        $order = Order::where('public_id', $publicOrderId)->first();
        
        $this->assertEquals(OrderStatus::Delivered, $order->status);
        $this->assertEquals(1, Order::count());
        $this->assertEquals(1, Key::where('order_id', $publicOrderId)->count());
        $this->assertEquals(1, Payment::where('order_public_id', $publicOrderId)->count());
    }
}
