<?php

namespace App\Livewire\HR\Payroll;

use App\Exports\PayrollExport;
use App\Models\HR\PayrollPeriod;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Payroll')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;
    public bool $showStats = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selected = $this->getPeriodsQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function toggleStats(): void
    {
        $this->showStats = !$this->showStats;
    }

    public function goToPreviousPage(): void
    {
        $this->previousPage();
    }

    public function goToNextPage(): void
    {
        $this->nextPage();
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new PayrollExport(), 'payroll-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new PayrollExport($this->selected), 'payroll-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getPeriodsQuery()
    {
        $query = PayrollPeriod::query()->withCount('items');

        if ($this->search) {
            $query->where('name', 'ilike', "%{$this->search}%");
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $query = match ($this->sort) {
            'oldest' => $query->orderBy('start_date', 'asc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'total_asc' => $query->orderBy('total_net', 'asc'),
            'total_desc' => $query->orderBy('total_net', 'desc'),
            default => $query->orderBy('start_date', 'desc'),
        };

        return $query;
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
        $periods = $this->getPeriodsQuery()->paginate(15);

        return view('livewire.hr.payroll.index', [
            'periods' => $periods,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
