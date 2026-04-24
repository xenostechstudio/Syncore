<?php

namespace App\Exports;

use App\Exports\Concerns\HasExportDefaults;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendancesExport implements FromQuery, WithHeadings, WithMapping
{
    use HasExportDefaults;

    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee',
            'Date',
            'Check In',
            'Check Out',
            'Status',
            'Late (min)',
            'Work Duration (min)',
            'Overtime (min)',
            'Schedule',
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->id,
            $attendance->employee->name,
            $attendance->date->format('Y-m-d'),
            $attendance->check_in_time ?? '-',
            $attendance->check_out_time ?? '-',
            $attendance->status,
            $attendance->late_minutes,
            $attendance->work_duration_minutes,
            $attendance->overtime_minutes,
            $attendance->workSchedule?->name ?? '-',
        ];
    }
}
