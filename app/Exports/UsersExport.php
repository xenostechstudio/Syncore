<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return User::with('roles')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '-',
                'role' => $user->roles->pluck('name')->implode(', ') ?: '-',
                'status' => $user->email_verified_at ? 'Active' : 'Inactive',
                'language' => $user->language ?? '-',
                'timezone' => $user->timezone ?? '-',
                'created_at' => $user->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Phone',
            'Role',
            'Status',
            'Language',
            'Timezone',
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
