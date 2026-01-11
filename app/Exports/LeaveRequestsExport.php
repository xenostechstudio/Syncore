<?php

namespace App\Exports;

use App\Models\HR\LeaveRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaveRequestsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return LeaveRequest::query()
            ->with(['employee', 'leaveType', 'approver'])
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($request) => [
                'employee' => $request->employee?->name ?? '-',
                'leave_type' => $request->leaveType?->name ?? '-',
                'start_date' => $request->start_date?->format('Y-m-d') ?? '-',
                'end_date' => $request->end_date?->format('Y-m-d') ?? '-',
                'days' => $request->days ?? '-',
                'reason' => $request->reason ?? '-',
                'status' => ucfirst($request->status),
                'approver' => $request->approver?->name ?? '-',
                'created_at' => $request->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Leave Type',
            'Start Date',
            'End Date',
            'Days',
            'Reason',
            'Status',
            'Approver',
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
