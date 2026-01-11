<?php

namespace App\Livewire\HR\Positions;

use App\Exports\PositionsExport;
use App\Imports\PositionsImport;
use App\Livewire\Concerns\WithImport;
use App\Models\HR\Department;
use App\Models\HR\Position;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Positions')]
class Index extends Component
{
    use WithPagination, WithImport;

    #[Url]
    public string $search = '';

    #[Url]
    public string $departmentId = '';

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
            $this->selected = $this->getPositionsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
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

        $positions = Position::whereIn('id', $this->selected)
            ->withCount('employees')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($positions as $position) {
            if ($position->employees_count === 0) {
                $canDelete[] = [
                    'id' => $position->id,
                    'name' => $position->name,
                ];
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

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new PositionsExport(), 'positions-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new PositionsExport($this->selected), 'positions-selected-' . now()->format('Y-m-d') . '.xlsx');
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

    protected function getPositionsQuery()
    {
        return Position::query()
            ->with(['department', 'employees'])
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->when($this->departmentId, fn($q) => $q->where('department_id', $this->departmentId))
            ->when($this->status !== '', fn($q) => $q->where('is_active', $this->status === 'active'));
    }

    public function render()
    {
        $query = $this->getPositionsQuery();

        // Apply sorting
        $query = match($this->sort) {
            'name_desc' => $query->orderBy('name', 'desc'),
            'latest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('name', 'asc'),
        };

        $positions = $query->paginate(15);

        return view('livewire.hr.positions.index', [
            'positions' => $positions,
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
