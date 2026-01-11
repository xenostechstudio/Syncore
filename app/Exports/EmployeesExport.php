<?php

namespace App\Exports;

use App\Models\HR\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Employee::query()
            ->with(['department', 'position'])
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($employee) => [
                'name' => $employee->name,
                'email' => $employee->email ?? '-',
                'phone' => $employee->phone ?? '-',
                'department' => $employee->department?->name ?? '-',
                'position' => $employee->position?->name ?? '-',
                'hire_date' => $employee->hire_date?->format('Y-m-d') ?? '-',
                'status' => ucfirst($employee->status),
                'created_at' => $employee->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Department',
            'Position',
            'Hire Date',
            'Status',
            'Created At',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
