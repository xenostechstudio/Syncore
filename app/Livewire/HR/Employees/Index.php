<?php

namespace App\Livewire\HR\Employees;

use App\Exports\EmployeesExport;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Position;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

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

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getEmployeesQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
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

    public function clearFilters(): void
    {
        $this->reset(['search', 'departmentId', 'status', 'sort', 'groupBy']);
        $this->resetPage();
        $this->clearSelection();
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $employees = Employee::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($employees as $employee) {
            if ($employee->status === 'terminated' || $employee->status === 'resigned') {
                $canDelete[] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'status' => $employee->status,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'reason' => "Status is '{$employee->status}' - only terminated/resigned employees can be deleted",
                ];
            }
        }

        $this->deleteValidation = [
            'canDelete' => $canDelete,
            'cannotDelete' => $cannotDelete,
            'totalSelected' => count($this->selected),
        ];

        $this->showDeleteConfirm = true;
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Employee::whereIn('id', $this->selected)
            ->whereIn('status', ['terminated', 'resigned'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} employees deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Employee::whereIn('id', $this->selected)->update(['status' => $status]);

        $this->clearSelection();
        session()->flash('success', "{$count} employees updated to {$status}.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new EmployeesExport(), 'employees-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new EmployeesExport($this->selected), 'employees-selected-' . now()->format('Y-m-d') . '.xlsx');
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
