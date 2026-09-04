<?php

namespace App\Http\Resources;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Payment\Enum\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'order_id' => $this->public_id,
            'sku' => $this->sku,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status->value,
            'status_delivery' => $this->getDeliveryStatus($this->status, $this->payment?->status),
        ];

        if ($this->code)
            $data['code'] = $this->code;

        return $data;
    }

    protected function getDeliveryStatus(
        OrderStatus $orderStatus, 
        ?PaymentStatus $paymentStatus
    ): string
    {
        if ($orderStatus == OrderStatus::Delivered && $paymentStatus == PaymentStatus::Paid)
            return "Выдан, оплачен";

        if ($orderStatus == OrderStatus::Delivered && ($paymentStatus == PaymentStatus::Failed || !$paymentStatus)) 
            return "Выдан, но не оплачен";

        if ($paymentStatus == PaymentStatus::Paid)
            return "Оплачен, но не выдан";

        return "Не выдан, не оплачен";
    }
}
