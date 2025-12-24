<?php

namespace App\Exports;

use App\Models\Delivery\DeliveryOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DeliveryOrdersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return DeliveryOrder::with(['salesOrder', 'warehouse', 'user'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($delivery) => [
                'delivery_number' => $delivery->delivery_number,
                'sales_order' => $delivery->salesOrder?->order_number ?? '-',
                'warehouse' => $delivery->warehouse?->name ?? '-',
                'delivery_date' => $delivery->delivery_date?->format('Y-m-d'),
                'status' => ucfirst(str_replace('_', ' ', $delivery->status->value ?? $delivery->status)),
                'recipient_name' => $delivery->recipient_name ?? '-',
                'recipient_phone' => $delivery->recipient_phone ?? '-',
                'shipping_address' => $delivery->shipping_address ?? '-',
                'courier' => $delivery->courier ?? '-',
                'tracking_number' => $delivery->tracking_number ?? '-',
                'assigned_to' => $delivery->user?->name ?? '-',
                'created_at' => $delivery->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Delivery Number',
            'Sales Order',
            'Warehouse',
            'Delivery Date',
            'Status',
            'Recipient Name',
            'Recipient Phone',
            'Shipping Address',
            'Courier',
            'Tracking Number',
            'Assigned To',
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
