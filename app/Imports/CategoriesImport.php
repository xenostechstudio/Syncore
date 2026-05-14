<?php

namespace App\Imports;

use App\Models\Inventory\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoriesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $name = trim($row['name'] ?? '');
                $code = trim($row['code'] ?? '');

                if (empty($name)) {
                    continue;
                }

                // Find parent category
                $parentId = null;
                if (!empty($row['parent'])) {
                    $parent = Category::where('name', 'ilike', trim($row['parent']))->first();
                    $parentId = $parent?->id;
                }

                // Check if category exists by code or name
                $category = !empty($code) 
                    ? Category::where('code', $code)->first() 
                    : Category::where('name', 'ilike', $name)->first();

                $data = [
                    'name' => $name,
                    'code' => $code ?: null,
                    'description' => $row['description'] ?? null,
                    'parent_id' => $parentId,
                    'color' => $row['color'] ?? null,
                    'is_active' => $this->parseBoolean($row['is_active'] ?? true),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                ];

                if ($category) {
                    $category->update($data);
                    $this->updated++;
                } else {
                    Category::create($data);
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
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'parent' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes', 'active', 'y'], true);
    }
    public function onFailure(\Maatwebsite\Excel\Validators\Failure ...$failures): void
    {
        foreach ($failures as $failure) {
            foreach ($failure->errors() as $message) {
                $this->errors[] = [
                    "row"       => $failure->row(),
                    "attribute" => $failure->attribute(),
                    "message"   => $message,
                    "values"    => $failure->values(),
                ];
            }
        }
    }
}
