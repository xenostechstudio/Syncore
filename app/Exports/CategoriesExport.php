<?php

namespace App\Exports;

use App\Models\Inventory\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoriesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Category::withCount('products')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'name' => $category->name,
                'description' => $category->description ?? '-',
                'products_count' => $category->products_count,
                'created_at' => $category->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Description',
            'Products Count',
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
