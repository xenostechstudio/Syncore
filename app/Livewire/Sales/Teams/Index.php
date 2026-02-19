<?php

namespace App\Livewire\Sales\Teams;

use App\Exports\SalesTeamsExport;
use App\Imports\SalesTeamsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithManualPagination;
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
    use WithManualPagination, WithImport;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $filter = 'active'; // 'active', 'archived', 'all'

    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Group By
    public string $groupBy = '';

    // Column visibility
    public array $visibleColumns = [
        'name' => true,
        'leader' => true,
        'members' => true,
        'target' => true,
        'status' => true,
    ];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getTeamsQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy']);
        $this->filter = 'active';
        $this->resetPage();
    }

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    // Archive confirmation
    public bool $showArchiveConfirm = false;

    // Bulk Actions
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

        // Only allow deletion of archived teams
        $teams = SalesTeam::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($teams as $team) {
            if (!$team->is_active) {
                $canDelete[] = [
                    'id' => $team->id,
                    'name' => $team->name,
                ];
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

        // Only delete archived teams
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

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new SalesTeamsExport(), 'sales-teams-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new SalesTeamsExport($this->selected), 'sales-teams-selected-' . now()->format('Y-m-d') . '.xlsx');
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

    private function getTeamsQuery()
    {
        return SalesTeam::query()
            ->with(['leader'])
            ->withCount('members')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->filter === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->filter === 'archived', fn($q) => $q->where('is_active', false))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'name_asc', fn($q) => $q->orderBy('name'))
            ->when($this->sort === 'name_desc', fn($q) => $q->orderByDesc('name'));
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
        $teams = $this->getTeamsQuery()->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.sales.teams.index', [
            'teams' => $teams,
        ]);
    }
}
