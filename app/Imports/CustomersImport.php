<?php

namespace App\Imports;

use App\Imports\Concerns\HasImportTracking;
use App\Models\Sales\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomersImport implements ToCollection, WithHeadingRow, WithValidation, WithChunkReading
{
    use HasImportTracking;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $name = trim($row['name'] ?? '');
                $email = trim($row['email'] ?? '');

                if (empty($name)) {
                    continue;
                }

                // Check if customer exists by email
                $customer = !empty($email) ? Customer::where('email', $email)->first() : null;

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
                    'notes' => $row['notes'] ?? null,
                    'is_active' => true,
                ];

                if ($customer) {
                    $customer->update($data);
                    $this->updated++;
                } else {
                    Customer::create($data);
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
            'company' => 'nullable|string|max:255',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
