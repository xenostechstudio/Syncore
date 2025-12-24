<?php

namespace App\Exports;

use App\Models\Invoicing\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoicesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Invoice::with(['customer', 'salesOrder'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($invoice) => [
                'invoice_number' => $invoice->invoice_number,
                'customer' => $invoice->customer?->name ?? '-',
                'sales_order' => $invoice->salesOrder?->order_number ?? '-',
                'invoice_date' => $invoice->invoice_date?->format('Y-m-d'),
                'due_date' => $invoice->due_date?->format('Y-m-d'),
                'status' => ucfirst($invoice->status),
                'subtotal' => $invoice->subtotal,
                'tax' => $invoice->tax,
                'discount' => $invoice->discount,
                'total' => $invoice->total,
                'paid_amount' => $invoice->paid_amount ?? 0,
                'paid_date' => $invoice->paid_date?->format('Y-m-d'),
                'created_at' => $invoice->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Invoice Number',
            'Customer',
            'Sales Order',
            'Invoice Date',
            'Due Date',
            'Status',
            'Subtotal',
            'Tax',
            'Discount',
            'Total',
            'Paid Amount',
            'Paid Date',
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
