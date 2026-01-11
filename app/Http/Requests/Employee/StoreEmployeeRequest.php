<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create employees');
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|string|max:50|unique:employees,employee_id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:1000',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'hire_date' => 'required|date',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
            'basic_salary' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'status' => 'nullable|in:active,inactive,terminated',
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'email.required' => 'Email is required.',
            'email.unique' => 'This email is already registered.',
            'employee_id.unique' => 'This employee ID is already in use.',
            'hire_date.required' => 'Hire date is required.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'basic_salary.min' => 'Basic salary cannot be negative.',
        ];
    }
}
