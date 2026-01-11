<?php

namespace App\Exports;

use App\Models\Purchase\VendorBill;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorBillsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return VendorBill::query()
            ->with('supplier')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('bill_date')
            ->get()
            ->map(fn ($bill) => [
                'bill_number' => $bill->bill_number,
                'supplier' => $bill->supplier?->name ?? '-',
                'vendor_reference' => $bill->vendor_reference ?? '-',
                'bill_date' => $bill->bill_date?->format('Y-m-d') ?? '-',
                'due_date' => $bill->due_date?->format('Y-m-d') ?? '-',
                'subtotal' => number_format($bill->subtotal ?? 0, 0),
                'tax' => number_format($bill->tax ?? 0, 0),
                'total' => number_format($bill->total ?? 0, 0),
                'status' => ucfirst($bill->status),
                'created_at' => $bill->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Bill Number',
            'Supplier',
            'Vendor Reference',
            'Bill Date',
            'Due Date',
            'Subtotal',
            'Tax',
            'Total',
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
