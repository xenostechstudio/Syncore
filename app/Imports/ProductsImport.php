<?php

namespace App\Imports;

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $sku = trim($row['sku'] ?? '');
                $name = trim($row['name'] ?? '');

                if (empty($name)) {
                    continue;
                }

                // Find or create category
                $categoryId = null;
                if (!empty($row['category'])) {
                    $category = Category::firstOrCreate(
                        ['name' => trim($row['category'])],
                        ['slug' => Str::slug(trim($row['category']))]
                    );
                    $categoryId = $category->id;
                }

                // Check if product exists by SKU
                $product = !empty($sku) ? Product::where('sku', $sku)->first() : null;

                $data = [
                    'name' => $name,
                    'sku' => $sku ?: null,
                    'category_id' => $categoryId,
                    'type' => $row['type'] ?? 'product',
                    'cost_price' => $this->parseNumber($row['cost_price'] ?? 0),
                    'selling_price' => $this->parseNumber($row['selling_price'] ?? 0),
                    'quantity' => (int) ($row['quantity'] ?? 0),
                    'min_stock' => (int) ($row['min_stock'] ?? 0),
                    'description' => $row['description'] ?? null,
                    'status' => $row['status'] ?? 'active',
                ];

                if ($product) {
                    $product->update($data);
                    $this->updated++;
                } else {
                    Product::create($data);
                    $this->imported++;
                }
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:255',
            'type' => 'nullable|in:product,service',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
        ];
    }

    private function parseNumber($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        // Remove currency symbols and thousands separators
        $cleaned = preg_replace('/[^0-9.,]/', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);
        
        return (float) $cleaned;
    }
}
