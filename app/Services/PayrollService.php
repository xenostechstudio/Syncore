<?php

namespace App\Services;

use App\Enums\PayrollState;
use App\Events\PayrollProcessed;
use App\Models\HR\Employee;
use App\Models\HR\PayrollItem;
use App\Models\HR\PayrollItemDetail;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\SalaryComponent;
use Illuminate\Support\Facades\DB;

/**
 * Payroll Service
 * 
 * Centralized business logic for payroll operations.
 */
class PayrollService
{
    /**
     * Create a new payroll period.
     */
    public function createPeriod(array $data): PayrollPeriod
    {
        return PayrollPeriod::create([
            'name' => $data['name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'payment_date' => $data['payment_date'] ?? null,
            'status' => PayrollState::DRAFT->value,
            'created_by' => auth()->id(),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Generate payroll items for all active employees.
     */
    public function generatePayrollItems(PayrollPeriod $period): int
    {
        if (!$period->state->canEdit()) {
            return 0;
        }

        $employees = Employee::where('status', 'active')
            ->whereNotNull('basic_salary')
            ->get();

        $count = 0;

        DB::transaction(function () use ($period, $employees, &$count) {
            foreach ($employees as $employee) {
                // Skip if already has payroll item
                if ($period->items()->where('employee_id', $employee->id)->exists()) {
                    continue;
                }

                $payrollItem = $this->createPayrollItem($period, $employee);
                if ($payrollItem) {
                    $count++;
                }
            }

            $period->recalculateTotals();
        });

        return $count;
    }

    /**
     * Create a payroll item for an employee.
     */
    public function createPayrollItem(PayrollPeriod $period, Employee $employee): PayrollItem
    {
        $payrollItem = PayrollItem::create([
            'payroll_period_id' => $period->id,
            'employee_id' => $employee->id,
            'basic_salary' => $employee->basic_salary,
            'total_earnings' => $employee->basic_salary,
            'total_deductions' => 0,
            'net_salary' => $employee->basic_salary,
        ]);

        // Add salary components
        $this->applySalaryComponents($payrollItem, $employee);

        return $payrollItem;
    }

    /**
     * Apply salary components to a payroll item.
     */
    protected function applySalaryComponents(PayrollItem $payrollItem, Employee $employee): void
    {
        $totalEarnings = $payrollItem->basic_salary;
        $totalDeductions = 0;

        // Get employee-specific components
        $employeeComponents = $employee->salaryComponents()->with('salaryComponent')->get();

        foreach ($employeeComponents as $empComponent) {
            $component = $empComponent->salaryComponent;
            $amount = $empComponent->amount;

            // Calculate percentage-based components
            if ($component->calculation_type === 'percentage') {
                $amount = $payrollItem->basic_salary * ($empComponent->amount / 100);
            }

            PayrollItemDetail::create([
                'payroll_item_id' => $payrollItem->id,
                'salary_component_id' => $component->id,
                'amount' => $amount,
            ]);

            if ($component->type === 'earning') {
                $totalEarnings += $amount;
            } else {
                $totalDeductions += $amount;
            }
        }

        // Get default components not assigned to employee
        $assignedComponentIds = $employeeComponents->pluck('salary_component_id');
        $defaultComponents = SalaryComponent::where('is_default', true)
            ->whereNotIn('id', $assignedComponentIds)
            ->get();

        foreach ($defaultComponents as $component) {
            $amount = $component->default_amount;

            if ($component->calculation_type === 'percentage') {
                $amount = $payrollItem->basic_salary * ($component->default_amount / 100);
            }

            PayrollItemDetail::create([
                'payroll_item_id' => $payrollItem->id,
                'salary_component_id' => $component->id,
                'amount' => $amount,
            ]);

            if ($component->type === 'earning') {
                $totalEarnings += $amount;
            } else {
                $totalDeductions += $amount;
            }
        }

        $payrollItem->update([
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => $totalEarnings - $totalDeductions,
        ]);
    }

    /**
     * Approve a payroll period.
     */
    public function approve(PayrollPeriod $period): bool
    {
        return $period->approve(auth()->id());
    }

    /**
     * Start processing a payroll period.
     */
    public function startProcessing(PayrollPeriod $period): bool
    {
        return $period->startProcessing();
    }

    /**
     * Mark payroll as paid.
     */
    public function markPaid(PayrollPeriod $period): bool
    {
        if (!$period->payment_date) {
            $period->payment_date = now();
            $period->save();
        }

        $result = $period->markAsPaid();

        if ($result) {
            // Dispatch event for notifications
            event(new PayrollProcessed($period));
        }

        return $result;
    }

    /**
     * Cancel a payroll period.
     */
    public function cancel(PayrollPeriod $period, ?string $reason = null): bool
    {
        return $period->cancelPayroll();
    }

    /**
     * Reset payroll to draft.
     */
    public function resetToDraft(PayrollPeriod $period): bool
    {
        return $period->resetToDraft();
    }

    /**
     * Recalculate a single payroll item.
     */
    public function recalculateItem(PayrollItem $item): void
    {
        $totalEarnings = $item->basic_salary;
        $totalDeductions = 0;

        foreach ($item->details as $detail) {
            if ($detail->salaryComponent->type === 'earning') {
                $totalEarnings += $detail->amount;
            } else {
                $totalDeductions += $detail->amount;
            }
        }

        $item->update([
            'total_earnings' => $totalEarnings,
            'total_deductions' => $totalDeductions,
            'net_salary' => $totalEarnings - $totalDeductions,
        ]);
    }
}
