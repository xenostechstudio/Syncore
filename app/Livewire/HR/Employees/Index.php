<?php

namespace App\Livewire\HR\Employees;

use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Position;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Employees')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $departmentId = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $groupBy = '';

    #[Url]
    public string $view = 'list';

    public bool $showStats = false;

    public array $selected = [];
    public bool $selectAll = false;

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getEmployeesQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setView(string $view): void
    {
        $this->view = $view;
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

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function getStatisticsProperty(): array
    {
        return [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'terminated' => Employee::where('status', 'terminated')->count(),
            'resigned' => Employee::where('status', 'resigned')->count(),
        ];
    }

    protected function getEmployeesQuery()
    {
        return Employee::query()
            ->with(['department', 'position', 'manager'])
            ->when($this->search, fn($q) => $q->where(function($q) {
                $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('email', 'ilike', "%{$this->search}%");
            }))
            ->when($this->departmentId, fn($q) => $q->where('department_id', $this->departmentId))
            ->when($this->status, fn($q) => $q->where('status', $this->status));
    }

    public function render()
    {
        $query = $this->getEmployeesQuery();

        // Apply sorting
        $query = match($this->sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'hire_date' => $query->orderBy('hire_date', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $employees = $query->paginate(15);

        return view('livewire.hr.employees.index', [
            'employees' => $employees,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'positions' => Position::where('is_active', true)->orderBy('name')->get(),
            'statistics' => $this->statistics,
        ]);
    }
}
