<?php

namespace App\Exports;

use App\Models\Accounting\Account;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Account::query()
            ->with('parent')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('code')
            ->get()
            ->map(fn ($account) => [
                'code' => $account->code,
                'name' => $account->name,
                'type' => ucfirst($account->type),
                'parent' => $account->parent?->name ?? '-',
                'description' => $account->description ?? '-',
                'status' => $account->is_active ? 'Active' : 'Inactive',
                'system' => $account->is_system ? 'Yes' : 'No',
                'created_at' => $account->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Code',
            'Name',
            'Type',
            'Parent Account',
            'Description',
            'Status',
            'System Account',
            'Created At',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
