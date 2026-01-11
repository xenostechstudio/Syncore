<?php

namespace App\Exports;

use App\Models\Purchase\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseOrdersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return PurchaseOrder::with(['supplier', 'user'])
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($order) => [
                'order_number' => $order->order_number,
                'supplier' => $order->supplier?->name ?? '-',
                'buyer' => $order->user?->name ?? '-',
                'order_date' => $order->order_date?->format('Y-m-d'),
                'expected_date' => $order->expected_date?->format('Y-m-d'),
                'status' => ucfirst(str_replace('_', ' ', $order->status)),
                'subtotal' => $order->subtotal,
                'tax' => $order->tax,
                'discount' => $order->discount,
                'total' => $order->total,
                'created_at' => $order->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Supplier',
            'Buyer',
            'Order Date',
            'Expected Date',
            'Status',
            'Subtotal',
            'Tax',
            'Discount',
            'Total',
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
