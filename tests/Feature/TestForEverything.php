<?php

namespace Tests\Feature;

use App\Domain\Money\Enum\MoneyMoveType;
use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Payment\Enum\PaymentStatus;
use App\Infrastructure\Models\MoneyMove;
use App\Infrastructure\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TestForEverything extends TestCase
{
    use DatabaseTransactions;

    public function test_delivered(): void
    {
        $order = Order::factory()->withItems(3)->create();

        $response = $this->post(route('webhook.payment'), $this->getRequestData($order));
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::Delivered, $order->status);
        
        $items = $order->items;
        $this->assertEquals(3, $items->count());
        foreach ($items as $item) {
            $this->assertNotNull($item->code);
            $this->assertEquals(OrderStatus::Delivered, $item->status);
        }

        $moneyMoves = MoneyMove::all();
        $this->assertEquals(1, $moneyMoves->where('type', MoneyMoveType::Received)->count());
        $this->assertEquals(3, $moneyMoves->where('type', MoneyMoveType::Issued)->count());
        $this->assertEquals($moneyMoves->where('type', MoneyMoveType::Received)->sum('amount'), $items->sum('amount'));
        $this->assertEquals($moneyMoves->where('type', MoneyMoveType::Issued)->sum('amount'), $items->sum('amount'));
    }

    public function test_partially(): void
    {
        $order = Order::factory()->withItems(3)->create();
        $order->items->get(1)->update(['sku' => 'FAIK_SKU']);
        $this->testPartillyWithResponseMessage($order, 'Order is partially delivered');
    }

    public function test_paymnet_repeat(): void
    {
        $order = Order::factory()->withItems(3)->create();
        $order->items->get(1)->update(['sku' => 'FAIK_SKU']);
        $this->testPartillyWithResponseMessage($order, 'Order is partially delivered');
        $this->testPartillyWithResponseMessage($order, 'already processed');
    }

    protected function testPartillyWithResponseMessage(Order $order, string $message): void
    {
        $response = $this->post(route('webhook.payment'), $this->getRequestData($order));
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->json('message'), $message));

        $order->refresh();
        $this->assertEquals(OrderStatus::PartiallyDelivered, $order->status);

        $items = $order->items;
        $successItems = $items->where('status', OrderStatus::Delivered);
        $failedItems = $items->where('status', OrderStatus::OutOfStock);
        $this->assertEquals(2, $successItems->count());
        $this->assertEquals(1, $failedItems->count());

        $moneyMoves = MoneyMove::all();

        $this->assertEquals(1, $moneyMoves->where('type', MoneyMoveType::Received)->count());
        $this->assertEquals(2, $moneyMoves->where('type', MoneyMoveType::Issued)->count());        
        $this->assertEquals(1, $moneyMoves->where('type', MoneyMoveType::Refund)->count());  
        $this->assertEquals(
            $moneyMoves->where('type', MoneyMoveType::Issued)->sum('amount'),
            $items->where('status', OrderStatus::Delivered)->sum('amount'),
        );
        $this->assertEquals(
            $moneyMoves->where('type', MoneyMoveType::Refund)->sum('amount'),
            $items->where('status', OrderStatus::OutOfStock)->sum('amount'),
        );
    }

    protected function getRequestData(Order $order): array
    {
        $request = [
            'event_id' => 'event_test',
            'order_id' => $order->public_id,
            'status' => PaymentStatus::Paid->value,
            'amount' => $order->items->sum('amount'),
            'currency' => 'RUN',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];
        
        return $request;
    }
}
