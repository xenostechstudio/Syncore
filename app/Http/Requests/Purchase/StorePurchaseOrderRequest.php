<?php

namespace App\Http\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Purchase Order Request
 * 
 * Validates data for creating a new purchase order (RFQ).
 */
class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_arrival' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:2000',
            
            // Items validation
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Please select a supplier.',
            'supplier_id.exists' => 'The selected supplier does not exist.',
            'order_date.required' => 'Order date is required.',
            'expected_arrival.after_or_equal' => 'Expected arrival must be on or after the order date.',
            'items.required' => 'Please add at least one item.',
            'items.min' => 'Please add at least one item.',
            'items.*.product_id.required' => 'Please select a product for each item.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
        ];
    }
}
