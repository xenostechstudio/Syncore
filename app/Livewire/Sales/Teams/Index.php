<?php

namespace App\Livewire\Sales\Teams;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Sales\SalesTeam;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Teams')]
class Index extends Component
{
    use WithManualPagination;

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

    public function deleteSelected(): void
    {
        SalesTeam::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected teams deleted successfully.');
    }

    private function getTeamsQuery()
    {
        return SalesTeam::query()
            ->with(['leader'])
            ->withCount('members')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%"))
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
