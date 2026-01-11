<?php

namespace App\Exports;

use App\Models\CRM\Opportunity;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpportunitiesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Opportunity::query()
            ->with(['customer', 'assignedTo', 'pipeline'])
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($opp) => [
                'name' => $opp->name,
                'customer' => $opp->customer?->name ?? '-',
                'pipeline' => $opp->pipeline?->name ?? '-',
                'stage' => ucfirst($opp->stage ?? '-'),
                'expected_revenue' => $opp->expected_revenue ? number_format($opp->expected_revenue, 0) : '-',
                'probability' => $opp->probability ? $opp->probability . '%' : '-',
                'expected_close' => $opp->expected_close_date?->format('Y-m-d') ?? '-',
                'assigned_to' => $opp->assignedTo?->name ?? '-',
                'status' => ucfirst($opp->status),
                'created_at' => $opp->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Customer',
            'Pipeline',
            'Stage',
            'Expected Revenue',
            'Probability',
            'Expected Close',
            'Assigned To',
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
