<?php

namespace App\Imports;

use App\Models\HR\Department;
use App\Models\HR\Position;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;

class PositionsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure
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

                // Find department by name if provided
                $departmentId = null;
                if (!empty($row['department'])) {
                    $department = Department::where('name', 'ilike', trim($row['department']))->first();
                    $departmentId = $department?->id;
                }

                $position = !empty($code) ? Position::where('code', $code)->first() : null;

                $data = [
                    'name' => $name,
                    'code' => $code ?: strtoupper(substr($name, 0, 3)),
                    'department_id' => $departmentId,
                    'description' => $row['description'] ?? null,
                    'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                ];

                if ($position) {
                    $position->update($data);
                    $this->updated++;
                } else {
                    Position::create($data);
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
            'department' => 'nullable|string|max:255',
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
