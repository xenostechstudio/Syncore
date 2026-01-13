<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Customer Request
 * 
 * Validates data for creating a new customer.
 */
class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['person', 'company'])],
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
            'salesperson_id' => 'nullable|exists:users,id',
            'payment_term_id' => 'nullable|exists:payment_terms,id',
            'payment_method' => 'nullable|string|max:50',
            'pricelist_id' => 'nullable|exists:pricelists,id',
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Please select customer type.',
            'type.in' => 'Customer type must be either person or company.',
            'name.required' => 'Customer name is required.',
            'email.email' => 'Please enter a valid email address.',
        ];
    }
}
