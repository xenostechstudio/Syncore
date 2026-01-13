<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update Product Request
 * 
 * Validates data for updating an existing product.
 */
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product') ?? $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'sku' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($productId),
            ],
            'product_type' => ['sometimes', Rule::in(['storable', 'consumable', 'service'])],
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
            'sku.unique' => 'This SKU is already in use by another product.',
            'barcode.unique' => 'This barcode is already in use by another product.',
            'product_type.in' => 'Product type must be storable, consumable, or service.',
            'cost_price.min' => 'Cost price cannot be negative.',
            'selling_price.min' => 'Selling price cannot be negative.',
        ];
    }
}
