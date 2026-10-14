<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'shipping_address_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:0,1'],
            'checkout_token' => ['required', 'uuid'],
            'is_direct_shipping' => ['sometimes', 'boolean'],
        ];
    }
}
