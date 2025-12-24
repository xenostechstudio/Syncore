<?php

namespace App\Exports;

use App\Models\Purchase\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SuppliersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Supplier::orderBy('name')
            ->get()
            ->map(fn ($supplier) => [
                'name' => $supplier->name,
                'email' => $supplier->email ?? '-',
                'phone' => $supplier->phone ?? '-',
                'company' => $supplier->company ?? '-',
                'address' => $supplier->address ?? '-',
                'city' => $supplier->city ?? '-',
                'country' => $supplier->country ?? '-',
                'status' => ucfirst($supplier->status ?? 'active'),
                'created_at' => $supplier->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Company',
            'Address',
            'City',
            'Country',
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
