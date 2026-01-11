<?php

namespace App\Livewire\HR\Departments;

use App\Exports\DepartmentsExport;
use App\Imports\DepartmentsImport;
use App\Livewire\Concerns\WithImport;
use App\Models\HR\Department;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Departments')]
class Index extends Component
{
    use WithPagination, WithImport;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'name_asc';

    #[Url]
    public string $view = 'grid';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getDepartmentsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function goToPreviousPage(): void
    {
        $this->previousPage();
    }

    public function goToNextPage(): void
    {
        $this->nextPage();
    }

    // Bulk Actions
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
                $canDelete[] = [
                    'id' => $dept->id,
                    'name' => $dept->name,
                ];
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

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new DepartmentsExport(), 'departments-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new DepartmentsExport($this->selected), 'departments-selected-' . now()->format('Y-m-d') . '.xlsx');
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

    protected function getDepartmentsQuery()
    {
        return Department::query()
            ->with(['parent', 'manager', 'employees'])
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('code', 'ilike', "%{$this->search}%"))
            ->when($this->status !== '', fn($q) => $q->where('is_active', $this->status === 'active'));
    }

    public function render()
    {
        $query = $this->getDepartmentsQuery();

        // Apply sorting
        $query = match($this->sort) {
            'name_desc' => $query->orderBy('name', 'desc'),
            'code' => $query->orderBy('code', 'asc'),
            'latest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('name', 'asc'),
        };

        $departments = $query->paginate(15);

        return view('livewire.hr.departments.index', [
            'departments' => $departments,
        ]);
    }
}
