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
        return [
            'total' => PayrollPeriod::count(),
            'draft' => PayrollPeriod::where('status', 'draft')->count(),
            'processing' => PayrollPeriod::where('status', 'processing')->count(),
            'approved' => PayrollPeriod::where('status', 'approved')->count(),
            'paid' => PayrollPeriod::where('status', 'paid')->count(),
            'total_amount' => PayrollPeriod::where('status', 'paid')->sum('total_net'),
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
