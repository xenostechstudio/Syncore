<?php

namespace App\Services\Reports;

use App\Models\HR\Employee;
use App\Models\HR\LeaveRequest;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * HR Report Service
 * 
 * Provides HR analytics including employee metrics, leave analysis,
 * payroll summaries, and attendance reports.
 */
class HRReportService
{
    /**
     * Get employee statistics by department.
     */
    public function getEmployeesByDepartment(): array
    {
        return Employee::query()
            ->select('department_id')
            ->selectRaw('COUNT(*) as employee_count')
            ->selectRaw("COUNT(CASE WHEN status = 'active' THEN 1 END) as active_count")
            ->with('department:id,name')
            ->groupBy('department_id')
            ->get()
            ->map(fn($item) => [
                'department' => $item->department?->name ?? 'Unassigned',
                'employee_count' => $item->employee_count,
                'active_count' => $item->active_count,
            ])
            ->toArray();
    }

    /**
     * Get employee turnover rate.
     */
    public function getTurnoverRate(Carbon $startDate, Carbon $endDate): array
    {
        $startCount = Employee::where('hire_date', '<', $startDate)
            ->where(function ($q) use ($startDate) {
                $q->whereNull('termination_date')
                    ->orWhere('termination_date', '>=', $startDate);
            })
            ->count();

        $endCount = Employee::where('hire_date', '<=', $endDate)
            ->where(function ($q) use ($endDate) {
                $q->whereNull('termination_date')
                    ->orWhere('termination_date', '>', $endDate);
            })
            ->count();

        $avgCount = ($startCount + $endCount) / 2;

        $separations = Employee::whereBetween('termination_date', [$startDate, $endDate])->count();
        $newHires = Employee::whereBetween('hire_date', [$startDate, $endDate])->count();

        $turnoverRate = $avgCount > 0 ? ($separations / $avgCount) * 100 : 0;

        return [
            'start_headcount' => $startCount,
            'end_headcount' => $endCount,
            'average_headcount' => round($avgCount),
            'new_hires' => $newHires,
            'separations' => $separations,
            'turnover_rate' => round($turnoverRate, 2),
        ];
    }

    /**
     * Get leave analysis by type.
     */
    public function getLeaveAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        return LeaveRequest::query()
            ->select('leave_type_id')
            ->selectRaw('COUNT(*) as request_count')
            ->selectRaw('SUM(days) as total_days')
            ->selectRaw("COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count")
            ->selectRaw("COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_count")
            ->selectRaw("COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count")
            ->with('leaveType:id,name')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('leave_type_id')
            ->get()
            ->map(fn($item) => [
                'leave_type' => $item->leaveType?->name ?? 'Unknown',
                'request_count' => $item->request_count,
                'total_days' => $item->total_days,
                'approved_count' => $item->approved_count,
                'rejected_count' => $item->rejected_count,
                'pending_count' => $item->pending_count,
                'approval_rate' => $item->request_count > 0 
                    ? round(($item->approved_count / $item->request_count) * 100, 1) 
                    : 0,
            ])
            ->toArray();
    }


    /**
     * Get payroll summary by period.
     */
    public function getPayrollSummary(Carbon $startDate, Carbon $endDate): array
    {
        return PayrollPeriod::query()
            ->selectRaw('COUNT(*) as period_count')
            ->selectRaw('SUM(total_gross) as total_gross')
            ->selectRaw('SUM(total_deductions) as total_deductions')
            ->selectRaw('SUM(total_net) as total_net')
            ->selectRaw("COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_count")
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->first()
            ->toArray();
    }

    /**
     * Get payroll cost by department.
     */
    public function getPayrollByDepartment(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('payroll_items')
            ->join('payroll_periods', 'payroll_items.payroll_period_id', '=', 'payroll_periods.id')
            ->join('employees', 'payroll_items.employee_id', '=', 'employees.id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select('departments.name as department')
            ->selectRaw('COUNT(DISTINCT payroll_items.employee_id) as employee_count')
            ->selectRaw('SUM(payroll_items.net_salary) as total_cost')
            ->selectRaw('AVG(payroll_items.net_salary) as avg_salary')
            ->whereBetween('payroll_periods.payment_date', [$startDate, $endDate])
            ->where('payroll_periods.status', 'paid')
            ->groupBy('departments.id', 'departments.name')
            ->orderByDesc('total_cost')
            ->get()
            ->map(fn($item) => [
                'department' => $item->department ?? 'Unassigned',
                'employee_count' => $item->employee_count,
                'total_cost' => $item->total_cost,
                'avg_salary' => round($item->avg_salary, 2),
            ])
            ->toArray();
    }

    /**
     * Get attendance summary.
     */
    public function getAttendanceSummary(Carbon $startDate, Carbon $endDate): array
    {
        $totalWorkDays = $startDate->diffInWeekdays($endDate);
        $totalEmployees = Employee::where('status', 'active')->count();
        $expectedAttendance = $totalWorkDays * $totalEmployees;

        $attendance = Attendance::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('COUNT(*) as total_records')
            ->selectRaw("COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count")
            ->selectRaw("COUNT(CASE WHEN status = 'absent' THEN 1 END) as absent_count")
            ->selectRaw("COUNT(CASE WHEN status = 'late' THEN 1 END) as late_count")
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (check_out - check_in))/3600) as avg_hours')
            ->first();

        return [
            'total_work_days' => $totalWorkDays,
            'total_employees' => $totalEmployees,
            'expected_attendance' => $expectedAttendance,
            'actual_attendance' => $attendance->total_records ?? 0,
            'present_count' => $attendance->present_count ?? 0,
            'absent_count' => $attendance->absent_count ?? 0,
            'late_count' => $attendance->late_count ?? 0,
            'avg_work_hours' => round($attendance->avg_hours ?? 0, 2),
            'attendance_rate' => $expectedAttendance > 0 
                ? round((($attendance->present_count ?? 0) / $expectedAttendance) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get HR summary metrics.
     */
    public function getSummary(): array
    {
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        
        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->count();

        $avgTenure = Employee::where('status', 'active')
            ->whereNotNull('hire_date')
            ->selectRaw('AVG(EXTRACT(YEAR FROM AGE(NOW(), hire_date))) as avg_years')
            ->value('avg_years');

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'pending_leaves' => $pendingLeaves,
            'on_leave_today' => $onLeaveToday,
            'avg_tenure_years' => round($avgTenure ?? 0, 1),
        ];
    }
}
