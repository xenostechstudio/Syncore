<?php

namespace App\Imports;

use App\Models\Sales\Tax;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TaxesImport implements ToCollection, WithHeadingRow, WithValidation
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

                $tax = !empty($code) ? Tax::where('code', $code)->first() : null;

                $data = [
                    'name' => $name,
                    'code' => $code ?: strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 10)),
                    'rate' => (float) ($row['rate'] ?? 0),
                    'type' => $row['type'] ?? 'percentage',
                    'scope' => $row['scope'] ?? 'sales',
                    'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                    'include_in_price' => isset($row['include_in_price']) ? filter_var($row['include_in_price'], FILTER_VALIDATE_BOOLEAN) : false,
                    'description' => $row['description'] ?? null,
                ];

                if ($tax) {
                    $tax->update($data);
                    $this->updated++;
                } else {
                    Tax::create($data);
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
            'rate' => 'nullable|numeric|min:0',
            'type' => 'nullable|in:percentage,fixed',
            'scope' => 'nullable|in:sales,purchase,both',
        ];
    }
}
