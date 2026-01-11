<?php

namespace App\Exports;

use App\Models\Sales\SalesTeam;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesTeamsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return SalesTeam::query()
            ->with('leader')
            ->withCount('members')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('name')
            ->get()
            ->map(fn ($team) => [
                'name' => $team->name,
                'leader' => $team->leader?->name ?? '-',
                'members' => $team->members_count,
                'target' => $team->target_amount ? number_format($team->target_amount, 0) : '-',
                'description' => $team->description ?? '-',
                'status' => $team->is_active ? 'Active' : 'Inactive',
                'created_at' => $team->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Leader',
            'Members',
            'Target Amount',
            'Description',
            'Status',
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
