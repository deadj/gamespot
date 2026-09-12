<?php

namespace App\Http\Resources;

use App\Infrastructure\Models\MoneyMove;
use App\Infrastructure\Models\Order;
use App\Infrastructure\Models\OrderHistory;
use App\Infrastructure\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderHistoryResource extends JsonResource
{
    protected const string TYPE_ORDER_STATUS_CHANGE = 'order_status_change';
    protected const string TYPE_ORDER_ITEM_STATUS_CHANGE = 'order_item_status_change';
    protected const string TYPE_MONEY_MOVE = 'money_move';
    
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_state' => [
                'order' => $this->orderState,
                'order_items' => $this->orderItemsState,
            ],
            'history' => $this->getFormattedHistory(),
            'financial_report' => $this->financialReport,
        ];
    }

    protected function getFormattedHistory(): array
    {
        $history = [];

        foreach ($this->history as $historyItem) {
            $historyItemData = [];

            if ($historyItem instanceof OrderHistory) {
                if ($historyItem->target_type == Order::morphClass()) {
                    $historyItemData['type'] = static::TYPE_ORDER_STATUS_CHANGE;
                    $historyItemData['order_public_id'] = $historyItem->target->public_id;
                } elseif ($historyItem->target_type == OrderItem::morphClass()) {
                    $historyItemData['type'] = static::TYPE_ORDER_ITEM_STATUS_CHANGE;
                    $historyItemData['order_public_id'] = $historyItem->target->order->public_id;
                    $historyItemData['order_item_public_id'] = $historyItem->target->public_id;
                }

                $historyItemData['from'] = $historyItem->old_status;
                $historyItemData['to'] = $historyItem->new_status;                
            } elseif ($historyItem instanceof MoneyMove) {
                $historyItemData['type'] = static::TYPE_MONEY_MOVE;
                $historyItemData['money_move_type'] = $historyItem->type->value;
                $historyItemData['amount'] = $historyItem->amount;
                $historyItemData['order_public_id'] = $historyItem->order->public_id;
                $historyItemData['order_item_public_id'] = $historyItem->orderItem?->public_id;                 
            }

            $historyItemData['created_at'] = $historyItem->created_at;
            $history[] = $historyItemData;
        }

        return $history;
    }
}
