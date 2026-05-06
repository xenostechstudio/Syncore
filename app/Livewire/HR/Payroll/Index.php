<?php

namespace App\Livewire\HR\Payroll;

use App\Exports\PayrollExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\HR\PayrollPeriod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Payroll')]
class Index extends Component
{
    use WithIndexComponent;

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'payroll-' . now()->format('Y-m-d') . '.xlsx'
            : 'payroll-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new PayrollExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return PayrollPeriod::query()
            ->withCount('items')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return PayrollPeriod::class;
    }

    protected function getStatistics(): array
    {
        // Single grouped scan with both COUNT and SUM(total_net) so the
        // paid-amount stat piggybacks. Was 5 separate WHERE...COUNT queries
        // plus a separate sum.
        $byStatus = PayrollPeriod::query()
            ->selectRaw('status, COUNT(*) as count, SUM(total_net) as total_sum')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'total'        => (int) $byStatus->sum('count'),
            'draft'        => (int) ($byStatus->get('draft')?->count ?? 0),
            'processing'   => (int) ($byStatus->get('processing')?->count ?? 0),
            'approved'     => (int) ($byStatus->get('approved')?->count ?? 0),
            'paid'         => (int) ($byStatus->get('paid')?->count ?? 0),
            'total_amount' => (float) ($byStatus->get('paid')?->total_sum ?? 0),
        ];
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->orderBy('start_date', 'asc'),
            'name_asc' => $this->getQuery()->orderBy('name', 'asc'),
            'name_desc' => $this->getQuery()->orderBy('name', 'desc'),
            'total_asc' => $this->getQuery()->orderBy('total_net', 'asc'),
            'total_desc' => $this->getQuery()->orderBy('total_net', 'desc'),
            default => $this->getQuery()->orderBy('start_date', 'desc'),
        };

        return view('livewire.hr.payroll.index', [
            'periods' => $query->paginate(15, ['*'], 'page', $this->page),
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
