<?php

namespace App\Imports;

use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Position;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeesImport implements ToCollection, WithHeadingRow, WithValidation
{
    public int $imported = 0;
    public int $updated = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $name = trim($row['name'] ?? '');
                $email = trim($row['email'] ?? '');

                if (empty($name)) {
                    continue;
                }

                // Find department
                $departmentId = null;
                if (!empty($row['department'])) {
                    $department = Department::where('name', 'ilike', trim($row['department']))->first();
                    $departmentId = $department?->id;
                }

                // Find position
                $positionId = null;
                if (!empty($row['position'])) {
                    $position = Position::where('name', 'ilike', trim($row['position']))->first();
                    $positionId = $position?->id;
                }

                // Check if employee exists by email
                $employee = !empty($email) ? Employee::where('email', $email)->first() : null;

                $data = [
                    'name' => $name,
                    'email' => $email ?: null,
                    'phone' => $row['phone'] ?? null,
                    'mobile' => $row['mobile'] ?? null,
                    'department_id' => $departmentId,
                    'position_id' => $positionId,
                    'hire_date' => $this->parseDate($row['hire_date'] ?? null),
                    'employment_type' => $row['employment_type'] ?? 'permanent',
                    'status' => $row['status'] ?? 'active',
                    'basic_salary' => $this->parseNumber($row['basic_salary'] ?? 0),
                    'bank_name' => $row['bank_name'] ?? null,
                    'bank_account_number' => $row['bank_account_number'] ?? null,
                    'bank_account_name' => $row['bank_account_name'] ?? null,
                    'address' => $row['address'] ?? null,
                    'city' => $row['city'] ?? null,
                ];

                if ($employee) {
                    $employee->update($data);
                    $this->updated++;
                } else {
                    Employee::create($data);
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
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'employment_type' => 'nullable|in:permanent,contract,probation,intern,freelance',
            'status' => 'nullable|in:active,inactive,terminated,resigned',
            'basic_salary' => 'nullable|numeric|min:0',
        ];
    }

    private function parseNumber($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        $cleaned = preg_replace('/[^0-9.,]/', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);
        
        return (float) $cleaned;
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
