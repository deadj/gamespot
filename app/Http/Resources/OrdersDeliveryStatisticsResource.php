<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrdersDeliveryStatisticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'queue' => $this->queue,
            'delivered' => $this->delivered,
            'partially_delivered' => $this->partiallyDelivered,
        ];
    }
}
