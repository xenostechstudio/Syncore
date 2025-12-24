<?php

namespace App\Exports;

use App\Models\Inventory\Warehouse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehousesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Warehouse::orderBy('name')
            ->get()
            ->map(fn ($warehouse) => [
                'name' => $warehouse->name,
                'code' => $warehouse->code ?? '-',
                'address' => $warehouse->address ?? '-',
                'city' => $warehouse->city ?? '-',
                'country' => $warehouse->country ?? '-',
                'is_active' => $warehouse->is_active ? 'Yes' : 'No',
                'created_at' => $warehouse->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Address',
            'City',
            'Country',
            'Active',
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
