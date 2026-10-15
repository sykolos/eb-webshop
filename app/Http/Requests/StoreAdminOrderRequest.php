<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('order.create-admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'partner_id' => ['required', 'integer', 'exists:users,id'],
            'shipping_address_id' => ['nullable', 'integer'],
            'shipping_receiver' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:255'],
            'shipping_phone' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:80'],
            'shipping_address' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:255'],
            'shipping_city' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:120'],
            'shipping_zipcode' => ['required_without:shipping_address_id', 'nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'boolean'],
            'is_direct_shipping' => ['nullable', 'boolean'],
            'initial_status' => ['required', 'string', Rule::in(array_diff(Order::statuses(), ['törölve']))],
            'note' => ['nullable', 'string', 'max:5000'],
            'status_note' => ['nullable', 'string', 'max:2000'],
            'confirm_order' => ['accepted'],
            'idempotency_key' => ['required', 'uuid'],
            'lines' => ['required', 'array', 'min:1', 'max:100'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }
}
