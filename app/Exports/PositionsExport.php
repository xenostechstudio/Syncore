<?php

namespace App\Exports;

use App\Models\HR\Position;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PositionsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Position::query()
            ->with('department')
            ->withCount('employees')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($position) => [
                'name' => $position->name,
                'department' => $position->department?->name ?? '-',
                'employees' => $position->employees_count,
                'min_salary' => $position->min_salary ? number_format($position->min_salary, 0) : '-',
                'max_salary' => $position->max_salary ? number_format($position->max_salary, 0) : '-',
                'status' => $position->is_active ? 'Active' : 'Inactive',
                'created_at' => $position->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Department',
            'Employees',
            'Min Salary',
            'Max Salary',
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
