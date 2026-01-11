<?php

namespace App\Imports;

use App\Models\Sales\PaymentTerm;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PaymentTermsImport implements ToCollection, WithHeadingRow, WithValidation
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

                $paymentTerm = !empty($code) ? PaymentTerm::where('code', $code)->first() : null;

                $data = [
                    'name' => $name,
                    'code' => $code ?: strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 10)),
                    'days' => (int) ($row['days'] ?? 0),
                    'description' => $row['description'] ?? null,
                    'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                ];

                if ($paymentTerm) {
                    $paymentTerm->update($data);
                    $this->updated++;
                } else {
                    PaymentTerm::create($data);
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
            'days' => 'nullable|integer|min:0',
        ];
    }
}
