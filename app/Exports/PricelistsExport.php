<?php

namespace App\Exports;

use App\Models\Sales\Pricelist;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PricelistsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Pricelist::query()
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($pricelist) => [
                'code' => $pricelist->code ?? '-',
                'name' => $pricelist->name,
                'currency' => $pricelist->currency ?? 'IDR',
                'type' => ucfirst($pricelist->type ?? '-'),
                'discount' => $pricelist->discount ? $pricelist->discount . '%' : '-',
                'start_date' => $pricelist->start_date?->format('Y-m-d') ?? '-',
                'end_date' => $pricelist->end_date?->format('Y-m-d') ?? '-',
                'is_active' => $pricelist->is_active ? 'Active' : 'Inactive',
                'description' => $pricelist->description ?? '-',
                'created_at' => $pricelist->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Currency',
            'Type',
            'Discount',
            'Start Date',
            'End Date',
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
