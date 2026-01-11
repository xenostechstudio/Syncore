<?php

namespace App\Imports;

use App\Models\HR\LeaveType;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LeaveTypesImport implements ToCollection, WithHeadingRow, WithValidation
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

                $leaveType = !empty($code) ? LeaveType::where('code', $code)->first() : null;

                $data = [
                    'name' => $name,
                    'code' => $code ?: strtoupper(substr($name, 0, 3)),
                    'days_per_year' => (int) ($row['days_per_year'] ?? 12),
                    'is_paid' => isset($row['is_paid']) ? filter_var($row['is_paid'], FILTER_VALIDATE_BOOLEAN) : true,
                    'requires_approval' => isset($row['requires_approval']) ? filter_var($row['requires_approval'], FILTER_VALIDATE_BOOLEAN) : true,
                    'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                    'description' => $row['description'] ?? null,
                ];

                if ($leaveType) {
                    $leaveType->update($data);
                    $this->updated++;
                } else {
                    LeaveType::create($data);
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
            'days_per_year' => 'nullable|integer|min:0',
        ];
    }
}
