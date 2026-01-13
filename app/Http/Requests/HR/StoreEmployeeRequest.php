<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Employee Request
 * 
 * Validates data for creating a new employee.
 */
class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'birth_date' => 'nullable|date|before:today',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed'])],
            'nationality' => 'nullable|string|max:100',
            'id_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            
            // Emergency contact
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'emergency_contact_relation' => 'nullable|string|max:100',
            
            // Employment details
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after:hire_date',
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'contract', 'intern'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'terminated', 'resigned'])],
            
            // Salary & banking
            'basic_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            
            // Other
            'user_id' => 'nullable|exists:users,id',
            'hr_responsible_id' => 'nullable|exists:employees,id',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Employee name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered to another employee.',
            'birth_date.before' => 'Birth date must be in the past.',
            'hire_date.required' => 'Hire date is required.',
            'contract_end_date.after' => 'Contract end date must be after hire date.',
            'employment_type.required' => 'Employment type is required.',
            'status.required' => 'Status is required.',
        ];
    }
}
