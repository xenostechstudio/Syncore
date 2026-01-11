<?php

namespace App\Imports;

use App\Models\Sales\SalesTeam;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SalesTeamsImport implements ToCollection, WithHeadingRow, WithValidation
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

                // Find leader by name
                $leaderId = null;
                if (!empty($row['leader'])) {
                    $leader = User::where('name', 'ilike', trim($row['leader']))->first();
                    $leaderId = $leader?->id;
                }

                $salesTeam = SalesTeam::where('name', 'ilike', $name)->first();

                $data = [
                    'name' => $name,
                    'description' => $row['description'] ?? null,
                    'leader_id' => $leaderId,
                    'target_amount' => (float) ($row['target_amount'] ?? 0),
                    'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                ];

                if ($salesTeam) {
                    $salesTeam->update($data);
                    $this->updated++;
                } else {
                    SalesTeam::create($data);
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
            'target_amount' => 'nullable|numeric|min:0',
        ];
    }
}
