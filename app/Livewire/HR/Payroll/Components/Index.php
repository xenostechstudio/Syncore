<?php

namespace App\Livewire\HR\Payroll\Components;

use App\Livewire\Concerns\WithIndexComponent;
use App\Models\HR\SalaryComponent;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Salary Components')]
class Index extends Component
{
    use WithIndexComponent;

    #[Url]
    public string $componentType = '';

    public function mount(): void
    {
        $this->sort = 'sort_order';
    }

    public function updatedComponentType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'groupBy', 'componentType']);
        $this->sort = 'sort_order';
        $this->resetPage();
        $this->clearSelection();
    }

    public function getActiveFilterCount(): int
    {
        $count = 0;
        if ($this->status !== '' && $this->status !== 'all') {
            $count++;
        }
        if ($this->sort !== 'sort_order') {
            $count++;
        }
        if ($this->groupBy !== '') {
            $count++;
        }
        if ($this->componentType !== '') {
            $count++;
        }

        return $count;
    }

    protected function getQuery()
    {
        return SalaryComponent::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->componentType, fn ($q) => $q->where('type', $this->componentType));
    }

    protected function getModelClass(): string
    {
        return SalaryComponent::class;
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
        $query = match ($this->sort) {
            'name_asc' => $this->getQuery()->orderBy('name', 'asc'),
            'name_desc' => $this->getQuery()->orderBy('name', 'desc'),
            'code_asc' => $this->getQuery()->orderBy('code', 'asc'),
            'code_desc' => $this->getQuery()->orderBy('code', 'desc'),
            'amount_asc' => $this->getQuery()->orderBy('default_amount', 'asc'),
            'amount_desc' => $this->getQuery()->orderBy('default_amount', 'desc'),
            default => $this->getQuery()->orderBy('sort_order')->orderBy('name'),
        };

        return view('livewire.hr.payroll.components.index', [
            'components' => $query->paginate(15, ['*'], 'page', $this->page),
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
