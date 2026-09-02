<?php

namespace App\Http\Resources;

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
        ];

        if ($this->code)
            $data['code'] = $this->code;

        return $data;
    }
}
