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

    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Filters
    public bool $filterActive = false;
    public bool $filterInactive = false;

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
        $this->reset(['search', 'status', 'sort', 'filterActive', 'filterInactive', 'groupBy']);
        $this->resetPage();
    }

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $teams = SalesTeam::whereIn('id', $this->selected)
            ->withCount('members')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($teams as $team) {
            if ($team->members_count === 0) {
                $canDelete[] = [
                    'id' => $team->id,
                    'name' => $team->name,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $team->id,
                    'name' => $team->name,
                    'reason' => "Has {$team->members_count} members",
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

        $teamsWithMembers = SalesTeam::whereIn('id', $this->selected)
            ->whereHas('members')
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $teamsWithMembers));

        if (empty($deletableIds)) {
            session()->flash('error', 'No teams can be deleted. All selected teams have members.');
            $this->cancelDelete();
            return;
        }

        $count = SalesTeam::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} teams deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkActivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = SalesTeam::whereIn('id', $this->selected)->update(['is_active' => true]);

        $this->clearSelection();
        session()->flash('success', "{$count} teams activated.");
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = SalesTeam::whereIn('id', $this->selected)->update(['is_active' => false]);

        $this->clearSelection();
        session()->flash('success', "{$count} teams deactivated.");
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
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('description', 'ilike', "%{$this->search}%"))
            ->when($this->filterActive, fn($q) => $q->where('is_active', true))
            ->when($this->filterInactive, fn($q) => $q->where('is_active', false))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'name_asc', fn($q) => $q->orderBy('name'))
            ->when($this->sort === 'name_desc', fn($q) => $q->orderByDesc('name'));
    }

    public function render()
    {
        $teams = $this->getTeamsQuery()->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.sales.teams.index', [
            'teams' => $teams,
        ]);
    }
}
