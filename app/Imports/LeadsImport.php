<?php

namespace App\Imports;

use App\Models\CRM\Lead;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class LeadsImport implements ToCollection, WithHeadingRow, WithValidation
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

                // Find assigned user
                $assignedTo = null;
                if (!empty($row['assigned_to'])) {
                    $user = User::where('name', 'ilike', trim($row['assigned_to']))
                        ->orWhere('email', 'ilike', trim($row['assigned_to']))
                        ->first();
                    $assignedTo = $user?->id;
                }

                // Check if lead exists by email
                $lead = !empty($email) ? Lead::where('email', $email)->first() : null;

                $data = [
                    'name' => $name,
                    'email' => $email ?: null,
                    'phone' => $row['phone'] ?? null,
                    'company_name' => $row['company_name'] ?? null,
                    'job_title' => $row['job_title'] ?? null,
                    'website' => $row['website'] ?? null,
                    'address' => $row['address'] ?? null,
                    'source' => $row['source'] ?? null,
                    'status' => $row['status'] ?? 'new',
                    'notes' => $row['notes'] ?? null,
                    'assigned_to' => $assignedTo,
                ];

                if ($lead) {
                    $lead->update($data);
                    $this->updated++;
                } else {
                    Lead::create($data);
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
            'company_name' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'source' => 'nullable|in:website,referral,cold_call,social_media,advertisement,trade_show,email_campaign,other',
            'status' => 'nullable|in:new,contacted,qualified,converted,lost',
        ];
    }
}
