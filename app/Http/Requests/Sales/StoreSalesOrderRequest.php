<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Store Sales Order Request
 * 
 * Validates data for creating a new sales order.
 * Can be used by both Livewire components and API controllers.
 */
class StoreSalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'payment_terms' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:2000',
            'terms' => 'nullable|string|max:2000',
            
            // Items validation
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_id' => 'nullable|exists:taxes,id',
            'items.*.discount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Please select a customer.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'order_date.required' => 'Order date is required.',
            'expected_delivery_date.after_or_equal' => 'Expected delivery date must be on or after the order date.',
            'items.required' => 'Please add at least one item.',
            'items.min' => 'Please add at least one item.',
            'items.*.product_id.required' => 'Please select a product for each item.',
            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
            'items.*.unit_price.required' => 'Unit price is required for each item.',
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'customer',
            'order_date' => 'order date',
            'expected_delivery_date' => 'expected delivery date',
            'items.*.product_id' => 'product',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
        ];
    }
}
