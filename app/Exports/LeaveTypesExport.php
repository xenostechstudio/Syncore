<?php

namespace App\Exports;

use App\Models\HR\LeaveType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveTypesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return LeaveType::query()
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($type) => [
                'code' => $type->code ?? '-',
                'name' => $type->name,
                'days_per_year' => $type->days_per_year,
                'is_paid' => $type->is_paid ? 'Yes' : 'No',
                'requires_approval' => $type->requires_approval ? 'Yes' : 'No',
                'is_active' => $type->is_active ? 'Active' : 'Inactive',
                'description' => $type->description ?? '-',
                'created_at' => $type->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Days Per Year',
            'Is Paid',
            'Requires Approval',
            'Status',
            'Description',
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
