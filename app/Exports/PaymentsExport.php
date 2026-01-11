<?php

namespace App\Exports;

use App\Models\Invoicing\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Payment::with(['invoice.customer'])
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('payment_date')
            ->get()
            ->map(fn ($payment) => [
                'payment_number' => $payment->payment_number ?? '-',
                'invoice_number' => $payment->invoice?->invoice_number ?? '-',
                'customer' => $payment->invoice?->customer?->name ?? '-',
                'payment_date' => $payment->payment_date?->format('Y-m-d'),
                'amount' => $payment->amount ?? 0,
                'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? '-')),
                'reference' => $payment->reference ?? '-',
                'status' => ucfirst($payment->status ?? '-'),
                'created_at' => $payment->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Payment Number',
            'Invoice Number',
            'Customer',
            'Payment Date',
            'Amount',
            'Payment Method',
            'Reference',
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
