<?php

namespace App\Exports;

use App\Models\HR\PayrollPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return PayrollPeriod::query()
            ->withCount('items')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('start_date')
            ->get()
            ->map(fn ($period) => [
                'name' => $period->name,
                'start_date' => $period->start_date?->format('Y-m-d') ?? '-',
                'end_date' => $period->end_date?->format('Y-m-d') ?? '-',
                'employees' => $period->items_count,
                'total_gross' => number_format($period->total_gross ?? 0, 0),
                'total_deductions' => number_format($period->total_deductions ?? 0, 0),
                'total_net' => number_format($period->total_net ?? 0, 0),
                'status' => ucfirst($period->status),
                'created_at' => $period->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Period Name',
            'Start Date',
            'End Date',
            'Employees',
            'Total Gross',
            'Total Deductions',
            'Total Net',
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
