<?php

namespace App\Livewire\HR;

use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\LeaveRequest;
use App\Models\HR\PayrollPeriod;
use App\Models\HR\Position;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('HR')]
class Index extends Component
{
    public function render()
    {
        // Employee status distribution — single grouped scan, was 4 separate
        // WHERE...COUNT queries (and totalEmployees was a duplicate of
        // activeEmployees).
        $employeesByStatus = Employee::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $activeEmployees = (int) ($employeesByStatus['active'] ?? 0);
        $inactiveEmployees = (int) ($employeesByStatus['inactive'] ?? 0);
        $onLeaveEmployees = (int) ($employeesByStatus['on_leave'] ?? 0);
        $totalEmployees = $activeEmployees;

        $totalDepartments = Department::where('is_active', true)->count();
        $totalPositions = Position::where('is_active', true)->count();

        // New employees this month
        $newEmployeesThisMonth = Employee::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $newEmployeesLastMonth = Employee::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        // Leave Request status distribution — single grouped scan, was 3
        // separate WHERE...COUNT queries.
        $leaveByStatus = LeaveRequest::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $pendingLeaveRequests = (int) ($leaveByStatus['pending'] ?? 0);
        $approvedLeaveRequests = (int) ($leaveByStatus['approved'] ?? 0);
        $rejectedLeaveRequests = (int) ($leaveByStatus['rejected'] ?? 0);
        $leaveRequestsThisMonth = LeaveRequest::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Employees on leave today
        $onLeaveToday = LeaveRequest::where('status', 'approved')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->count();

        // Payroll Stats
        $currentPayroll = PayrollPeriod::where('status', 'draft')
            ->orWhere('status', 'approved')
            ->orWhere('status', 'processing')
            ->latest()
            ->first();
        
        $totalPayrollThisMonth = PayrollPeriod::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('status', 'paid')
            ->sum('total_net');

        $payrollPending = PayrollPeriod::whereIn('status', ['draft', 'approved', 'processing'])->count();
        $payrollPaid = PayrollPeriod::where('status', 'paid')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->count();

        // Department distribution
        $departmentDistribution = Department::where('is_active', true)
            ->withCount(['employees' => fn($q) => $q->where('status', 'active')])
            ->orderByDesc('employees_count')
            ->limit(5)
            ->get();

        // Recent employees
        $recentEmployees = Employee::with(['department', 'position'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent leave requests
        $recentLeaveRequests = LeaveRequest::with(['employee', 'leaveType'])
            ->latest()
            ->limit(5)
            ->get();

        // Upcoming birthdays (next 30 days)
        $upcomingBirthdays = Employee::where('status', 'active')
            ->whereNotNull('birth_date')
            ->get()
            ->filter(function ($employee) {
                $birthday = $employee->birth_date->setYear(now()->year);
                if ($birthday->isPast()) {
                    $birthday = $birthday->addYear();
                }
                return $birthday->diffInDays(now()) <= 30;
            })
            ->sortBy(function ($employee) {
                $birthday = $employee->birth_date->setYear(now()->year);
                if ($birthday->isPast()) {
                    $birthday = $birthday->addYear();
                }
                return $birthday;
            })
            ->take(5);

        // Monthly headcount trend (last 6 months)
        $headcountTrend = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);
            $count = Employee::where('status', 'active')
                ->whereDate('created_at', '<=', $date->endOfMonth())
                ->count();
            return [
                'month' => $date->format('M'),
                'count' => $count,
            ];
        });

        return view('livewire.hr.index', [
            'totalEmployees' => $totalEmployees,
            'totalDepartments' => $totalDepartments,
            'totalPositions' => $totalPositions,
            'newEmployeesThisMonth' => $newEmployeesThisMonth,
            'newEmployeesLastMonth' => $newEmployeesLastMonth,
            'activeEmployees' => $activeEmployees,
            'inactiveEmployees' => $inactiveEmployees,
            'onLeaveEmployees' => $onLeaveEmployees,
            'pendingLeaveRequests' => $pendingLeaveRequests,
            'approvedLeaveRequests' => $approvedLeaveRequests,
            'rejectedLeaveRequests' => $rejectedLeaveRequests,
            'leaveRequestsThisMonth' => $leaveRequestsThisMonth,
            'onLeaveToday' => $onLeaveToday,
            'currentPayroll' => $currentPayroll,
            'totalPayrollThisMonth' => $totalPayrollThisMonth,
            'payrollPending' => $payrollPending,
            'payrollPaid' => $payrollPaid,
            'departmentDistribution' => $departmentDistribution,
            'recentEmployees' => $recentEmployees,
            'recentLeaveRequests' => $recentLeaveRequests,
            'upcomingBirthdays' => $upcomingBirthdays,
            'headcountTrend' => $headcountTrend,
        ]);
    }
}
