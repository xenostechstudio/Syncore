<?php

namespace App\Exports;

use App\Models\HR\PayrollItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollSlipsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;
    protected ?int $periodId;

    public function __construct(?array $ids = null, ?int $periodId = null)
    {
        $this->ids = $ids;
        $this->periodId = $periodId;
    }

    public function collection()
    {
        return PayrollItem::with(['employee', 'period'])
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->when($this->periodId, fn($q) => $q->where('payroll_period_id', $this->periodId))
            ->orderBy('id')
            ->get()
            ->map(fn ($item) => [
                'period' => $item->period?->name ?? '-',
                'employee_id' => $item->employee?->employee_id ?? '-',
                'employee_name' => $item->employee?->name ?? '-',
                'department' => $item->employee?->department?->name ?? '-',
                'position' => $item->employee?->position?->name ?? '-',
                'basic_salary' => $item->basic_salary ?? 0,
                'total_allowances' => $item->total_allowances ?? 0,
                'total_deductions' => $item->total_deductions ?? 0,
                'gross_salary' => $item->gross_salary ?? 0,
                'net_salary' => $item->net_salary ?? 0,
                'status' => ucfirst($item->status ?? '-'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Period',
            'Employee ID',
            'Employee Name',
            'Department',
            'Position',
            'Basic Salary',
            'Total Allowances',
            'Total Deductions',
            'Gross Salary',
            'Net Salary',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
