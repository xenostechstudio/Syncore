<?php

namespace App\Livewire\HR\Employees;

use App\Exports\EmployeesExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Livewire\Concerns\WithPermissions;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\Position;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Employees')]
class Index extends Component
{
    use WithIndexComponent, WithPermissions;

    #[Url]
    public string $departmentId = '';

    public function updatedDepartmentId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'departmentId', 'status', 'sort', 'groupBy']);
        $this->resetPage();
        $this->clearSelection();
    }

    protected function getCustomActiveFilterCount(): int
    {
        return $this->departmentId !== '' ? 1 : 0;
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $employees = Employee::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($employees as $employee) {
            $statusValue = $employee->status?->value ?? $employee->status;
            if ($statusValue === 'inactive') {
                $canDelete[] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'status' => $statusValue,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'reason' => "Status is '{$statusValue}' - only inactive employees can be deleted",
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
        $this->authorizePermission('hr.delete');

        if (empty($this->selected)) {
            return;
        }

        $count = Employee::whereIn('id', $this->selected)
            ->where('status', 'inactive')
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} employees deleted successfully.");
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
        $filename = empty($this->selected)
            ? 'employees-' . now()->format('Y-m-d') . '.xlsx'
            : 'employees-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new EmployeesExport($this->selected ?: null), $filename);
    }

    public function getStatisticsProperty(): array
    {
        // Single grouped scan instead of four separate WHERE...COUNT queries.
        $byStatus = Employee::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'total'    => (int) $byStatus->sum(),
            'active'   => (int) ($byStatus['active'] ?? 0),
            'on_leave' => (int) ($byStatus['on_leave'] ?? 0),
            'inactive' => (int) ($byStatus['inactive'] ?? 0),
        ];
    }

    protected function getQuery()
    {
        return Employee::query()
            ->with(['department', 'position', 'manager'])
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->departmentId, fn ($q) => $q->where('department_id', $this->departmentId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return Employee::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->orderBy('created_at', 'asc'),
            'name_asc' => $this->getQuery()->orderBy('name', 'asc'),
            'name_desc' => $this->getQuery()->orderBy('name', 'desc'),
            'hire_date' => $this->getQuery()->orderBy('hire_date', 'desc'),
            default => $this->getQuery()->orderBy('created_at', 'desc'),
        };

        return view('livewire.hr.employees.index', [
            'employees' => $query->paginate(15, ['*'], 'page', $this->page),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'positions' => Position::where('is_active', true)->orderBy('name')->get(),
            'statistics' => $this->statistics,
        ]);
    }
}
