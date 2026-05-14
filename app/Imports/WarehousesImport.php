<?php

namespace App\Imports;

use App\Models\Inventory\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class WarehousesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $name = trim($row['name'] ?? '');

                if (empty($name)) {
                    continue;
                }

                $warehouse = Warehouse::where('name', 'ilike', $name)->first();

                $data = [
                    'name' => $name,
                    'location' => $row['location'] ?? null,
                    'contact_info' => $row['contact_info'] ?? null,
                ];

                if ($warehouse) {
                    $warehouse->update($data);
                    $this->updated++;
                } else {
                    Warehouse::create($data);
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
            'location' => 'nullable|string|max:500',
        ];
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
