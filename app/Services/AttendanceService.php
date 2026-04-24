<?php

namespace App\Services;

use App\Models\HR\Attendance;
use App\Models\HR\AttendanceSetting;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeSchedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AttendanceService
{
    public function checkIn(Employee $employee, array $data = []): Attendance
    {
        $date = Carbon::today();
        
        // Get or create attendance record
        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $date,
            ],
            [
                'work_schedule_id' => $this->getEmployeeSchedule($employee, $date)?->work_schedule_id,
                'status' => 'absent',
            ]
        );

        $attendance->checkIn($data);

        return $attendance->fresh();
    }

    public function checkOut(Employee $employee, array $data = []): Attendance
    {
        $date = Carbon::today();
        
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $date)
            ->firstOrFail();

        $attendance->checkOut($data);

        return $attendance->fresh();
    }

    public function getEmployeeSchedule(Employee $employee, Carbon $date): ?EmployeeSchedule
    {
        return EmployeeSchedule::getActiveScheduleForEmployee($employee->id, $date);
    }

    public function getTodayAttendance(Employee $employee): ?Attendance
    {
        return Attendance::where('employee_id', $employee->id)
            ->where('date', Carbon::today())
            ->first();
    }

    public function getMonthlyAttendance(Employee $employee, int $year, int $month): Collection
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();
    }

    public function getAttendanceSummary(Employee $employee, int $year, int $month): array
    {
        $attendances = $this->getMonthlyAttendance($employee, $year, $month);

        return [
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'on_leave' => $attendances->where('status', 'on_leave')->count(),
            'total_late_minutes' => $attendances->sum('late_minutes'),
            'total_overtime_minutes' => $attendances->sum('overtime_minutes'),
            'total_work_minutes' => $attendances->sum('work_duration_minutes'),
        ];
    }

    public function createManualAttendance(Employee $employee, Carbon $date, array $data): Attendance
    {
        $schedule = $this->getEmployeeSchedule($employee, $date);

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'work_schedule_id' => $schedule?->work_schedule_id,
            'check_in_time' => $data['check_in_time'] ?? null,
            'check_out_time' => $data['check_out_time'] ?? null,
            'status' => $data['status'] ?? 'absent',
            'is_manual' => true,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($attendance->check_in_time && $attendance->check_out_time) {
            $attendance->calculateWorkDuration();
        }

        if ($attendance->check_in_time) {
            $attendance->calculateStatus();
        }

        return $attendance;
    }

    public function canCheckIn(Employee $employee): bool
    {
        $today = Attendance::where('employee_id', $employee->id)
            ->where('date', Carbon::today())
            ->first();

        return !$today || !$today->check_in_time;
    }

    public function canCheckOut(Employee $employee): bool
    {
        $today = Attendance::where('employee_id', $employee->id)
            ->where('date', Carbon::today())
            ->first();

        return $today && $today->check_in_time && !$today->check_out_time;
    }
}
