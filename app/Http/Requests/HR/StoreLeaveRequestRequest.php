<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Leave Request Request
 * 
 * Validates data for creating a new leave request.
 */
class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'half_day' => 'boolean',
            'half_day_type' => [
                'nullable',
                Rule::requiredIf($this->boolean('half_day')),
                Rule::in(['morning', 'afternoon']),
            ],
            'reason' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee is required.',
            'leave_type_id.required' => 'Please select a leave type.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or in the future.',
            'end_date.required' => 'End date is required.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'half_day_type.required_if' => 'Please specify morning or afternoon for half day leave.',
        ];
    }
}
