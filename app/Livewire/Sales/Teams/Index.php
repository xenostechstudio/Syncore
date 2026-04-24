<?php

namespace App\Livewire\Sales\Teams;

use App\Exports\SalesTeamsExport;
use App\Imports\SalesTeamsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Sales\SalesTeam;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Teams')]
class Index extends Component
{
    use WithIndexComponent, WithImport;

    #[Url]
    public string $filter = 'active';

    public bool $showArchiveConfirm = false;

    public array $visibleColumns = [
        'name' => true,
        'leader' => true,
        'members' => true,
        'target' => true,
        'status' => true,
    ];

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
        $this->clearSelection();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy']);
        $this->filter = 'active';
        $this->resetPage();
        $this->clearSelection();
    }

    public function confirmBulkArchive(): void
    {
        if (empty($this->selected)) {
            return;
        }
        $this->showArchiveConfirm = true;
    }

    public function bulkArchive(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = SalesTeam::whereIn('id', $this->selected)->update(['is_active' => false]);

        $this->showArchiveConfirm = false;
        $this->clearSelection();
        session()->flash('success', "{$count} teams archived successfully.");
    }

    public function cancelArchive(): void
    {
        $this->showArchiveConfirm = false;
    }

    public function bulkRestore(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = SalesTeam::whereIn('id', $this->selected)->update(['is_active' => true]);

        $this->clearSelection();
        session()->flash('success', "{$count} teams restored successfully.");
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $teams = SalesTeam::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($teams as $team) {
            if (! $team->is_active) {
                $canDelete[] = ['id' => $team->id, 'name' => $team->name];
            } else {
                $cannotDelete[] = [
                    'id' => $team->id,
                    'name' => $team->name,
                    'reason' => 'Must be archived first',
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

        $deletableIds = SalesTeam::whereIn('id', $this->selected)
            ->where('is_active', false)
            ->pluck('id')
            ->toArray();

        if (empty($deletableIds)) {
            session()->flash('error', 'No teams can be deleted. Teams must be archived first.');
            $this->cancelDelete();
            return;
        }

        $count = SalesTeam::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} teams deleted permanently.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'sales-teams-' . now()->format('Y-m-d') . '.xlsx'
            : 'sales-teams-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new SalesTeamsExport($this->selected ?: null), $filename);
    }

    protected function getImportClass(): string
    {
        return SalesTeamsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['name', 'description', 'leader', 'target_amount', 'is_active'],
            'filename' => 'sales-teams-template.csv',
        ];
    }

    protected function getQuery()
    {
        return SalesTeam::query()
            ->with(['leader'])
            ->withCount('members')
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->when($this->filter === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->filter === 'archived', fn ($q) => $q->where('is_active', false));
    }

    protected function getModelClass(): string
    {
        return SalesTeam::class;
    }

    public function getActiveCountProperty(): int
    {
        return SalesTeam::where('is_active', true)->count();
    }

    public function getArchivedCountProperty(): int
    {
        return SalesTeam::where('is_active', false)->count();
    }

    public function render()
    {
        $query = $this->applySorting($this->getQuery());

        return view('livewire.sales.teams.index', [
            'teams' => $query->paginate(12, ['*'], 'page', $this->page),
        ]);
    }
}
