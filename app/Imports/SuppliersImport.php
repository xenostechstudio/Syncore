<?php

namespace App\Imports;

use App\Models\Purchase\Supplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SuppliersImport implements ToCollection, WithHeadingRow, WithValidation
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

                // Check if supplier exists by email
                $supplier = !empty($email) ? Supplier::where('email', $email)->first() : null;

                $data = [
                    'name' => $name,
                    'email' => $email ?: null,
                    'phone' => $row['phone'] ?? null,
                    'company' => $row['company'] ?? null,
                    'address' => $row['address'] ?? null,
                    'city' => $row['city'] ?? null,
                    'state' => $row['state'] ?? null,
                    'postal_code' => $row['postal_code'] ?? null,
                    'country' => $row['country'] ?? 'Indonesia',
                    'tax_id' => $row['tax_id'] ?? null,
                    'bank_name' => $row['bank_name'] ?? null,
                    'bank_account' => $row['bank_account'] ?? null,
                    'notes' => $row['notes'] ?? null,
                    'is_active' => true,
                ];

                if ($supplier) {
                    $supplier->update($data);
                    $this->updated++;
                } else {
                    Supplier::create($data);
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
        ];
    }
}
