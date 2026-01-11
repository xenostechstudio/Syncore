<?php

namespace App\Http\Requests\LeaveRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create leave requests');
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Please select an employee.',
            'leave_type_id.required' => 'Please select a leave type.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or in the future.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'reason.required' => 'Please provide a reason for the leave request.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->start_date && $this->end_date) {
                $start = \Carbon\Carbon::parse($this->start_date);
                $end = \Carbon\Carbon::parse($this->end_date);
                $days = $start->diffInDays($end) + 1;

                // Check leave balance
                if ($this->employee_id && $this->leave_type_id) {
                    $balance = \App\Models\HR\LeaveBalance::where('employee_id', $this->employee_id)
                        ->where('leave_type_id', $this->leave_type_id)
                        ->where('year', $start->year)
                        ->first();

                    if ($balance) {
                        $available = $balance->allocated + $balance->carried_over - $balance->used;
                        if ($days > $available) {
                            $validator->errors()->add('days', "Insufficient leave balance. Available: {$available} days, Requested: {$days} days.");
                        }
                    }
                }
            }
        });
    }
}
