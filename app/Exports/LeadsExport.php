<?php

namespace App\Exports;

use App\Models\CRM\Lead;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Lead::query()
            ->with('assignedTo')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($lead) => [
                'name' => $lead->name,
                'email' => $lead->email ?? '-',
                'phone' => $lead->phone ?? '-',
                'company' => $lead->company_name ?? '-',
                'source' => ucfirst($lead->source ?? '-'),
                'assigned_to' => $lead->assignedTo?->name ?? '-',
                'expected_revenue' => $lead->expected_revenue ? number_format($lead->expected_revenue, 0) : '-',
                'status' => ucfirst($lead->status),
                'created_at' => $lead->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Company',
            'Source',
            'Assigned To',
            'Expected Revenue',
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
