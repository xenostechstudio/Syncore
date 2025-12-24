<?php

namespace App\Exports;

use App\Models\Inventory\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Product::with(['category'])
            ->orderBy('name')
            ->get()
            ->map(fn ($product) => [
                'sku' => $product->sku ?? '-',
                'name' => $product->name,
                'category' => $product->category?->name ?? '-',
                'type' => ucfirst($product->type ?? '-'),
                'cost_price' => $product->cost_price ?? 0,
                'selling_price' => $product->selling_price ?? 0,
                'quantity' => $product->quantity ?? 0,
                'min_stock' => $product->min_stock ?? 0,
                'status' => ucfirst(str_replace('_', ' ', $product->status)),
                'created_at' => $product->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Name',
            'Category',
            'Type',
            'Cost Price',
            'Selling Price',
            'Quantity',
            'Min Stock',
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
