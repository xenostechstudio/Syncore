<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Product Request
 * 
 * Validates data for creating a new product.
 */
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'product_type' => ['required', Rule::in(['storable', 'consumable', 'service'])],
            'internal_reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:2000',
            'category_id' => 'nullable|exists:categories,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'responsible_id' => 'nullable|exists:users,id',
            
            // Pricing
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'sales_tax_id' => 'nullable|exists:taxes,id',
            
            // Physical attributes
            'weight' => 'nullable|numeric|min:0',
            'volume' => 'nullable|numeric|min:0',
            
            // Lead times
            'customer_lead_time' => 'nullable|integer|min:0',
            
            // Notes
            'receipt_note' => 'nullable|string|max:1000',
            'delivery_note' => 'nullable|string|max:1000',
            'internal_notes' => 'nullable|string|max:2000',
            
            // Status
            'status' => ['nullable', Rule::in(['active', 'inactive', 'discontinued'])],
            'is_favorite' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'sku.required' => 'SKU is required.',
            'sku.unique' => 'This SKU is already in use.',
            'barcode.unique' => 'This barcode is already in use.',
            'product_type.required' => 'Product type is required.',
            'product_type.in' => 'Product type must be storable, consumable, or service.',
            'cost_price.min' => 'Cost price cannot be negative.',
            'selling_price.min' => 'Selling price cannot be negative.',
        ];
    }
}
