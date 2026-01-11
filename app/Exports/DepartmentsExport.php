<?php

namespace App\Exports;

use App\Models\HR\Department;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DepartmentsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Department::query()
            ->with(['parent', 'manager'])
            ->withCount('employees')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($dept) => [
                'name' => $dept->name,
                'code' => $dept->code ?? '-',
                'parent' => $dept->parent?->name ?? '-',
                'manager' => $dept->manager?->name ?? '-',
                'employees' => $dept->employees_count,
                'status' => $dept->is_active ? 'Active' : 'Inactive',
                'created_at' => $dept->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Parent Department',
            'Manager',
            'Employees',
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
