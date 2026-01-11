<?php

namespace App\Exports;

use App\Models\Inventory\InventoryAdjustment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdjustmentsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return InventoryAdjustment::query()
            ->with(['warehouse', 'user'])
            ->withCount('items')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($adj) => [
                'adjustment_number' => $adj->adjustment_number,
                'warehouse' => $adj->warehouse?->name ?? '-',
                'type' => ucfirst($adj->adjustment_type ?? '-'),
                'adjustment_date' => $adj->adjustment_date?->format('Y-m-d') ?? '-',
                'items' => $adj->items_count,
                'reason' => $adj->reason ?? '-',
                'status' => ucfirst($adj->status),
                'created_by' => $adj->user?->name ?? '-',
                'created_at' => $adj->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Adjustment Number',
            'Warehouse',
            'Type',
            'Adjustment Date',
            'Items',
            'Reason',
            'Status',
            'Created By',
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
