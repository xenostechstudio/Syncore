<?php

namespace App\Livewire\HR\Departments;

use App\Exports\DepartmentsExport;
use App\Imports\DepartmentsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithPermissions;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\HR\Department;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Departments')]
class Index extends Component
{
    use WithIndexComponent, WithImport, WithPermissions;

    public function mount(): void
    {
        $this->sort = 'name_asc';
        $this->view = 'grid';
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $departments = Department::whereIn('id', $this->selected)
            ->withCount('employees')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($departments as $dept) {
            if ($dept->employees_count === 0) {
                $canDelete[] = ['id' => $dept->id, 'name' => $dept->name];
            } else {
                $cannotDelete[] = [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'reason' => "Has {$dept->employees_count} employees",
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

        $deptsWithEmployees = Department::whereIn('id', $this->selected)
            ->whereHas('employees')
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $deptsWithEmployees));

        if (empty($deletableIds)) {
            session()->flash('error', 'No departments can be deleted. All have employees.');
            $this->cancelDelete();
            return;
        }

        $count = Department::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} departments deleted successfully.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'departments-' . now()->format('Y-m-d') . '.xlsx'
            : 'departments-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new DepartmentsExport($this->selected ?: null), $filename);
    }

    protected function getImportClass(): string
    {
        return DepartmentsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['name', 'code', 'description', 'is_active'],
            'filename' => 'departments-template.csv',
        ];
    }

    protected function getQuery()
    {
        return Department::query()
            ->with(['parent', 'manager', 'employees'])
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->status !== '', fn ($q) => $q->where('is_active', $this->status === 'active'));
    }

    protected function getModelClass(): string
    {
        return Department::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'name_desc' => $this->getQuery()->orderBy('name', 'desc'),
            'code' => $this->getQuery()->orderBy('code', 'asc'),
            'latest' => $this->getQuery()->orderBy('created_at', 'desc'),
            default => $this->getQuery()->orderBy('name', 'asc'),
        };

        return view('livewire.hr.departments.index', [
            'departments' => $query->paginate(15, ['*'], 'page', $this->page),
        ]);
    }
}
