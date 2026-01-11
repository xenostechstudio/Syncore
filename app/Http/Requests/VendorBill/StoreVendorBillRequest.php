<?php

namespace App\Http\Requests\VendorBill;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create vendor bills');
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_rfq_id' => 'nullable|exists:purchase_rfqs,id',
            'vendor_reference' => 'nullable|string|max:100',
            'bill_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:bill_date',
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
            'bill_date.required' => 'Bill date is required.',
            'due_date.after_or_equal' => 'Due date must be on or after the bill date.',
            'items.required' => 'At least one item is required.',
            'items.*.quantity.min' => 'Quantity must be greater than zero.',
        ];
    }
}
