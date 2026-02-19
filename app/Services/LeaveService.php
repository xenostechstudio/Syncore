<?php

namespace App\Services;

use App\Enums\LeaveRequestState;
use App\Events\LeaveRequestApproved;
use App\Events\LeaveRequestRejected;
use App\Models\HR\Employee;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Leave Service
 * 
 * Centralized business logic for leave request operations.
 * Handles leave requests, approvals, rejections, and balance management.
 * 
 * @package App\Services
 */
class LeaveService
{
    /**
     * Create a new leave request.
     *
     * @param Employee $employee
     * @param array $data
     * @return LeaveRequest
     * @throws \Exception
     */
    public function createRequest(Employee $employee, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($employee, $data) {
            $leaveType = LeaveType::findOrFail($data['leave_type_id']);
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);
            $days = $this->calculateLeaveDays($startDate, $endDate, $data['half_day'] ?? false);

            // Check balance if leave type requires it
            if ($leaveType->requires_balance) {
                $balance = $this->getBalance($employee, $leaveType);
                if ($balance < $days) {
                    throw new \Exception("Insufficient leave balance. Available: {$balance} days, Requested: {$days} days");
                }
            }

            // Check for overlapping requests
            if ($this->hasOverlappingRequest($employee, $startDate, $endDate)) {
                throw new \Exception('You already have a leave request for this period.');
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'days' => $days,
                'half_day' => $data['half_day'] ?? false,
                'half_day_type' => $data['half_day_type'] ?? null,
                'reason' => $data['reason'] ?? null,
                'status' => LeaveRequestState::PENDING->value,
            ]);

            return $leaveRequest->fresh(['employee', 'leaveType']);
        });
    }

    /**
     * Approve a leave request.
     *
     * @param LeaveRequest $leaveRequest
     * @param int $approverId
     * @param string|null $notes
     * @return bool
     */
    public function approve(LeaveRequest $leaveRequest, int $approverId, ?string $notes = null): bool
    {
        if ($leaveRequest->status !== LeaveRequestState::PENDING->value) {
            return false;
        }

        return DB::transaction(function () use ($leaveRequest, $approverId, $notes) {
            $leaveRequest->approval_notes = $notes;
            $leaveRequest->save();

            $result = $leaveRequest->approve($approverId);

            // Deduct from balance if required (model handles event dispatch)
            if ($result && $leaveRequest->leaveType->requires_balance) {
                $this->deductBalance(
                    $leaveRequest->employee,
                    $leaveRequest->leaveType,
                    $leaveRequest->days
                );
            }

            return $result;
        });
    }

    /**
     * Reject a leave request.
     *
     * @param LeaveRequest $leaveRequest
     * @param int $approverId
     * @param string|null $reason
     * @return bool
     */
    public function reject(LeaveRequest $leaveRequest, int $approverId, ?string $reason = null): bool
    {
        return $leaveRequest->reject($approverId, $reason ?? '');
    }

    /**
     * Cancel a leave request.
     *
     * @param LeaveRequest $leaveRequest
     * @param string|null $reason
     * @return bool
     */
    public function cancel(LeaveRequest $leaveRequest, ?string $reason = null): bool
    {
        if (!in_array($leaveRequest->status, [LeaveRequestState::PENDING->value, LeaveRequestState::APPROVED->value])) {
            return false;
        }

        return DB::transaction(function () use ($leaveRequest, $reason) {
            $wasApproved = $leaveRequest->status === LeaveRequestState::APPROVED->value;

            $leaveRequest->cancellation_reason = $reason;
            $leaveRequest->save();

            $result = $leaveRequest->cancel();

            // Restore balance if was approved
            if ($result && $wasApproved && $leaveRequest->leaveType->requires_balance) {
                $this->restoreBalance(
                    $leaveRequest->employee,
                    $leaveRequest->leaveType,
                    $leaveRequest->days
                );
            }

            return $result;
        });
    }

    /**
     * Calculate leave days between two dates.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param bool $halfDay
     * @return float
     */
    public function calculateLeaveDays(Carbon $startDate, Carbon $endDate, bool $halfDay = false): float
    {
        if ($halfDay) {
            return 0.5;
        }

        $days = 0;
        $current = $startDate->copy();

        while ($current <= $endDate) {
            if (!$current->isWeekend()) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    /**
     * Get leave balance for an employee and leave type.
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @param int|null $year
     * @return float
     */
    public function getBalance(Employee $employee, LeaveType $leaveType, ?int $year = null): float
    {
        $year = $year ?? now()->year;

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', $year)
            ->first();

        if (!$balance) {
            // Create default balance
            $balance = LeaveBalance::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $year,
                'allocated' => $leaveType->default_days ?? 0,
                'used' => 0,
                'pending' => 0,
            ]);
        }

        return $balance->allocated - $balance->used;
    }

    /**
     * Deduct from leave balance.
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @param float $days
     * @return void
     */
    protected function deductBalance(Employee $employee, LeaveType $leaveType, float $days): void
    {
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->first();

        if ($balance) {
            $balance->increment('used', $days);
        }
    }

    /**
     * Restore leave balance (for cancelled approved requests).
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @param float $days
     * @return void
     */
    protected function restoreBalance(Employee $employee, LeaveType $leaveType, float $days): void
    {
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->first();

        if ($balance) {
            $balance->decrement('used', $days);
        }
    }

    /**
     * Check if employee has overlapping leave request.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int|null $excludeId
     * @return bool
     */
    public function hasOverlappingRequest(Employee $employee, Carbon $startDate, Carbon $endDate, ?int $excludeId = null): bool
    {
        return LeaveRequest::where('employee_id', $employee->id)
            ->whereNotIn('status', [LeaveRequestState::REJECTED->value, LeaveRequestState::CANCELLED->value])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }

    /**
     * Get leave summary for an employee.
     *
     * @param Employee $employee
     * @param int|null $year
     * @return array
     */
    public function getEmployeeSummary(Employee $employee, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->get();

        $pendingRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', LeaveRequestState::PENDING->value)
            ->count();

        return [
            'balances' => $balances->map(fn($b) => [
                'leave_type' => $b->leaveType->name,
                'allocated' => $b->allocated,
                'used' => $b->used,
                'remaining' => $b->allocated - $b->used,
            ])->toArray(),
            'pending_requests' => $pendingRequests,
            'year' => $year,
        ];
    }
}
