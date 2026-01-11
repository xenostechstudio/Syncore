<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create purchase orders');
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_arrival' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Please select a supplier.',
            'order_date.required' => 'Order date is required.',
            'expected_arrival.after_or_equal' => 'Expected arrival must be on or after the order date.',
            'items.required' => 'At least one item is required.',
            'items.*.product_id.required' => 'Please select a product for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}
