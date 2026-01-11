<?php

namespace App\Imports;

use App\Models\Sales\Pricelist;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PricelistsImport implements ToCollection, WithHeadingRow, WithValidation
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

                $pricelist = !empty($code) ? Pricelist::where('code', $code)->first() : null;

                $data = [
                    'name' => $name,
                    'code' => $code ?: strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 10)),
                    'currency' => $row['currency'] ?? 'IDR',
                    'type' => $row['type'] ?? 'fixed',
                    'discount' => (float) ($row['discount'] ?? 0),
                    'start_date' => !empty($row['start_date']) ? $row['start_date'] : null,
                    'end_date' => !empty($row['end_date']) ? $row['end_date'] : null,
                    'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                    'description' => $row['description'] ?? null,
                ];

                if ($pricelist) {
                    $pricelist->update($data);
                    $this->updated++;
                } else {
                    Pricelist::create($data);
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
            'currency' => 'nullable|string|max:3',
            'type' => 'nullable|in:fixed,percentage',
            'discount' => 'nullable|numeric|min:0',
        ];
    }
}
