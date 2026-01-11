<?php

namespace App\Imports;

use App\Models\CRM\Opportunity;
use App\Models\CRM\Pipeline;
use App\Models\Sales\Customer;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class OpportunitiesImport implements ToCollection, WithHeadingRow, WithValidation
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

                // Find customer by name
                $customerId = null;
                if (!empty($row['customer'])) {
                    $customer = Customer::where('name', 'ilike', trim($row['customer']))->first();
                    $customerId = $customer?->id;
                }

                // Find pipeline stage by name
                $pipelineId = null;
                if (!empty($row['stage'])) {
                    $pipeline = Pipeline::where('name', 'ilike', trim($row['stage']))->first();
                    $pipelineId = $pipeline?->id;
                }

                // Find assigned user by name
                $assignedTo = null;
                if (!empty($row['assigned_to'])) {
                    $user = User::where('name', 'ilike', trim($row['assigned_to']))->first();
                    $assignedTo = $user?->id;
                }

                $data = [
                    'name' => $name,
                    'customer_id' => $customerId,
                    'pipeline_id' => $pipelineId,
                    'expected_revenue' => (float) ($row['expected_revenue'] ?? 0),
                    'probability' => (float) ($row['probability'] ?? 50),
                    'expected_close_date' => !empty($row['expected_close_date']) ? $row['expected_close_date'] : null,
                    'description' => $row['description'] ?? null,
                    'assigned_to' => $assignedTo,
                ];

                // Check for existing opportunity by name and customer
                $opportunity = Opportunity::where('name', 'ilike', $name)
                    ->where('customer_id', $customerId)
                    ->first();

                if ($opportunity) {
                    $opportunity->update($data);
                    $this->updated++;
                } else {
                    Opportunity::create($data);
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
            'expected_revenue' => 'nullable|numeric|min:0',
            'probability' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
