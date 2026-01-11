<?php

namespace App\Exports;

use App\Models\Sales\Tax;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaxesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Tax::query()
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($tax) => [
                'code' => $tax->code ?? '-',
                'name' => $tax->name,
                'type' => ucfirst($tax->type),
                'rate' => $tax->type === 'percentage' ? $tax->rate . '%' : $tax->rate,
                'scope' => ucfirst($tax->scope ?? '-'),
                'include_in_price' => $tax->include_in_price ? 'Yes' : 'No',
                'is_active' => $tax->is_active ? 'Active' : 'Inactive',
                'description' => $tax->description ?? '-',
                'created_at' => $tax->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Type',
            'Rate',
            'Scope',
            'Include in Price',
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
