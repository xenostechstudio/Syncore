<?php

namespace App\Exports;

use App\Models\Sales\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Customer::orderBy('name')
            ->get()
            ->map(fn ($customer) => [
                'name' => $customer->name,
                'email' => $customer->email ?? '-',
                'phone' => $customer->phone ?? '-',
                'company' => $customer->company ?? '-',
                'address' => $customer->address ?? '-',
                'city' => $customer->city ?? '-',
                'country' => $customer->country ?? '-',
                'status' => ucfirst($customer->status),
                'created_at' => $customer->created_at?->format('Y-m-d H:i'),
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
