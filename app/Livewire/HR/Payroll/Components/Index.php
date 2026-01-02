<?php

namespace App\Livewire\HR\Payroll\Components;

use App\Models\HR\SalaryComponent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Salary Components')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $componentType = '';

    #[Url]
    public string $sort = 'sort_order';

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
            $this->selected = $this->getComponentsQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
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

    protected function getComponentsQuery()
    {
        $query = SalaryComponent::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('code', 'ilike', "%{$this->search}%");
            });
        }

        if ($this->componentType) {
            $query->where('type', $this->componentType);
        }

        $query = match ($this->sort) {
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'code_asc' => $query->orderBy('code', 'asc'),
            'code_desc' => $query->orderBy('code', 'desc'),
            'amount_asc' => $query->orderBy('default_amount', 'asc'),
            'amount_desc' => $query->orderBy('default_amount', 'desc'),
            default => $query->orderBy('sort_order')->orderBy('name'),
        };

        return $query;
    }

    protected function getStatistics(): array
    {
        return [
            'total' => SalaryComponent::count(),
            'earnings' => SalaryComponent::where('type', 'earning')->count(),
            'deductions' => SalaryComponent::where('type', 'deduction')->count(),
            'active' => SalaryComponent::where('is_active', true)->count(),
            'taxable' => SalaryComponent::where('is_taxable', true)->count(),
        ];
    }

    public function render()
    {
        $components = $this->getComponentsQuery()->paginate(15);

        return view('livewire.hr.payroll.components.index', [
            'components' => $components,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
