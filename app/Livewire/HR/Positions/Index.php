<?php

namespace App\Livewire\HR\Positions;

use App\Exports\PositionsExport;
use App\Imports\PositionsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithPermissions;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\HR\Department;
use App\Models\HR\Position;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Positions')]
class Index extends Component
{
    use WithIndexComponent, WithImport, WithPermissions;

    #[Url]
    public string $departmentId = '';

    public function mount(): void
    {
        $this->sort = 'name_asc';
        $this->view = 'grid';
    }

    public function updatedDepartmentId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'groupBy', 'departmentId']);
        $this->sort = 'name_asc';
        $this->resetPage();
        $this->clearSelection();
    }

    public function getActiveFilterCount(): int
    {
        $count = 0;
        if ($this->status !== '' && $this->status !== 'all') {
            $count++;
        }
        if ($this->sort !== 'name_asc') {
            $count++;
        }
        if ($this->groupBy !== '') {
            $count++;
        }
        if ($this->departmentId !== '') {
            $count++;
        }

        return $count;
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $positions = Position::whereIn('id', $this->selected)
            ->withCount('employees')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($positions as $position) {
            if ($position->employees_count === 0) {
                $canDelete[] = ['id' => $position->id, 'name' => $position->name];
            } else {
                $cannotDelete[] = [
                    'id' => $position->id,
                    'name' => $position->name,
                    'reason' => "Has {$position->employees_count} employees",
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

        $positionsWithEmployees = Position::whereIn('id', $this->selected)
            ->whereHas('employees')
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $positionsWithEmployees));

        if (empty($deletableIds)) {
            session()->flash('error', 'No positions can be deleted. All have employees.');
            $this->cancelDelete();
            return;
        }

        $count = Position::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} positions deleted successfully.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'positions-' . now()->format('Y-m-d') . '.xlsx'
            : 'positions-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new PositionsExport($this->selected ?: null), $filename);
    }

    protected function getImportClass(): string
    {
        return PositionsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['name', 'code', 'department', 'description', 'is_active'],
            'filename' => 'positions-template.csv',
        ];
    }

    protected function getQuery()
    {
        return Position::query()
            ->with(['department', 'employees'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->departmentId, fn ($q) => $q->where('department_id', $this->departmentId))
            ->when($this->status !== '', fn ($q) => $q->where('is_active', $this->status === 'active'));
    }

    protected function getModelClass(): string
    {
        return Position::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'name_desc' => $this->getQuery()->orderBy('name', 'desc'),
            'latest' => $this->getQuery()->orderBy('created_at', 'desc'),
            default => $this->getQuery()->orderBy('name', 'asc'),
        };

        return view('livewire.hr.positions.index', [
            'positions' => $query->paginate(15, ['*'], 'page', $this->page),
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
