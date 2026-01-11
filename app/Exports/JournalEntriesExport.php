<?php

namespace App\Exports;

use App\Models\Accounting\JournalEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JournalEntriesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return JournalEntry::query()
            ->with('createdBy')
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('entry_date')
            ->get()
            ->map(fn ($entry) => [
                'entry_number' => $entry->entry_number,
                'entry_date' => $entry->entry_date?->format('Y-m-d') ?? '-',
                'reference' => $entry->reference ?? '-',
                'description' => $entry->description ?? '-',
                'total_debit' => number_format($entry->total_debit ?? 0, 0),
                'total_credit' => number_format($entry->total_credit ?? 0, 0),
                'status' => ucfirst($entry->status),
                'created_by' => $entry->createdBy?->name ?? '-',
                'created_at' => $entry->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Entry Number',
            'Entry Date',
            'Reference',
            'Description',
            'Total Debit',
            'Total Credit',
            'Status',
            'Created By',
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
