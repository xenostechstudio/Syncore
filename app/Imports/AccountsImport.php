<?php

namespace App\Imports;

use App\Models\Accounting\Account;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AccountsImport implements ToCollection, WithHeadingRow, WithValidation
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

                if (empty($name) || empty($code)) {
                    continue;
                }

                $account = Account::where('code', $code)->first();

                // Find parent account by code if provided
                $parentId = null;
                if (!empty($row['parent_code'])) {
                    $parent = Account::where('code', trim($row['parent_code']))->first();
                    $parentId = $parent?->id;
                }

                $data = [
                    'code' => $code,
                    'name' => $name,
                    'type' => $row['type'] ?? 'asset',
                    'parent_id' => $parentId,
                    'description' => $row['description'] ?? null,
                    'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                    'is_system' => isset($row['is_system']) ? filter_var($row['is_system'], FILTER_VALIDATE_BOOLEAN) : false,
                ];

                if ($account) {
                    $account->update($data);
                    $this->updated++;
                } else {
                    Account::create($data);
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
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'nullable|in:asset,liability,equity,revenue,expense',
        ];
    }
}
