<?php

namespace App\Exports;

use App\Models\Inventory\InventoryTransfer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransfersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return InventoryTransfer::query()
            ->with(['sourceWarehouse', 'destinationWarehouse', 'user'])
            ->withCount('items')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($transfer) => [
                'transfer_number' => $transfer->transfer_number,
                'source' => $transfer->sourceWarehouse?->name ?? '-',
                'destination' => $transfer->destinationWarehouse?->name ?? '-',
                'transfer_date' => $transfer->transfer_date?->format('Y-m-d') ?? '-',
                'items' => $transfer->items_count,
                'notes' => $transfer->notes ?? '-',
                'status' => ucfirst($transfer->status),
                'created_by' => $transfer->user?->name ?? '-',
                'created_at' => $transfer->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Transfer Number',
            'Source Warehouse',
            'Destination Warehouse',
            'Transfer Date',
            'Items',
            'Notes',
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
