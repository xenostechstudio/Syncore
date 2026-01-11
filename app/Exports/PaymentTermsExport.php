<?php

namespace App\Exports;

use App\Models\Sales\PaymentTerm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentTermsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return PaymentTerm::query()
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($term) => [
                'code' => $term->code ?? '-',
                'name' => $term->name,
                'days' => $term->days,
                'is_active' => $term->is_active ? 'Active' : 'Inactive',
                'sort_order' => $term->sort_order,
                'description' => $term->description ?? '-',
                'created_at' => $term->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Days',
            'Status',
            'Sort Order',
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
