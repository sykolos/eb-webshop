<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Products;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('product.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_id' => ['required', 'integer', 'exists:product_units,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:4096'],
            'serialnum' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['nullable', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:255'],
            'is_published' => ['nullable', 'boolean'],
            'availability_status' => ['required', Rule::in(Products::availabilityStatuses())],
            'specifications' => ['nullable', 'array'],
            'specifications_text' => ['nullable', 'string', 'max:20000'],
            'filter_value_ids' => ['nullable', 'array'],
            'filter_value_ids.*' => ['integer', 'exists:filter_values,id'],
        ];
    }
}
