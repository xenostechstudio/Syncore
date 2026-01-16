<?php

namespace App\Exports;

use App\Models\Sales\Promotion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PromotionsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected ?array $ids;

    public function __construct(?array $ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        return Promotion::query()
            ->with(['reward'])
            ->when($this->ids, fn($q) => $q->whereIn('id', $this->ids))
            ->orderBy('priority')
            ->get()
            ->map(fn ($promotion) => [
                'name' => $promotion->name,
                'code' => $promotion->code ?? '',
                'type' => $promotion->type,
                'priority' => $promotion->priority,
                'is_combinable' => $promotion->is_combinable ? 'Yes' : 'No',
                'requires_coupon' => $promotion->requires_coupon ? 'Yes' : 'No',
                'start_date' => $promotion->start_date?->format('Y-m-d') ?? '',
                'end_date' => $promotion->end_date?->format('Y-m-d') ?? '',
                'usage_limit' => $promotion->usage_limit ?? '',
                'usage_per_customer' => $promotion->usage_per_customer ?? '',
                'min_order_amount' => $promotion->min_order_amount ?? '',
                'min_quantity' => $promotion->min_quantity ?? '',
                'is_active' => $promotion->is_active ? 'Active' : 'Inactive',
                'reward_type' => $promotion->reward?->reward_type ?? '',
                'discount_value' => $promotion->reward?->discount_value ?? '',
                'max_discount' => $promotion->reward?->max_discount ?? '',
                'buy_quantity' => $promotion->reward?->buy_quantity ?? '',
                'get_quantity' => $promotion->reward?->get_quantity ?? '',
                'apply_to' => $promotion->reward?->apply_to ?? '',
                'description' => $promotion->description ?? '',
                'usage_count' => $promotion->usage_count ?? 0,
                'created_at' => $promotion->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Code',
            'Type',
            'Priority',
            'Combinable',
            'Requires Coupon',
            'Start Date',
            'End Date',
            'Usage Limit',
            'Per Customer',
            'Min Order Amount',
            'Min Quantity',
            'Status',
            'Reward Type',
            'Discount Value',
            'Max Discount',
            'Buy Quantity',
            'Get Quantity',
            'Apply To',
            'Description',
            'Usage Count',
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
