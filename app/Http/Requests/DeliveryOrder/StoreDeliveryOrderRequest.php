<?php

namespace App\Http\Requests\DeliveryOrder;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create delivery orders');
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'delivery_date' => 'required|date',
            'shipping_address' => 'nullable|string|max:1000',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_phone' => 'nullable|string|max:50',
            'courier' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'sales_order_id.required' => 'Please select a sales order.',
            'warehouse_id.required' => 'Please select a warehouse.',
            'items.required' => 'At least one item is required.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}
