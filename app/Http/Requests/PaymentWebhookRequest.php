<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'string'],
            'order_id' => ['required', 'string'],
            'status' => ['required', 'string', 'in:paid,failed'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string'],
            'created_at' => ['required', 'date'],
        ];
    }
}
