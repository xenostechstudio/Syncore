<?php

namespace App\Livewire\HR\Leave\Types;

use App\Models\HR\LeaveType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Leave Types')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'name_asc';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
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

    public function deleteSelected(): void
    {
        LeaveType::whereIn('id', $this->selected)->delete();
        session()->flash('success', count($this->selected) . ' leave type(s) deleted.');
        $this->clearSelection();
    }

    protected function getQuery()
    {
        return LeaveType::query()
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('code', 'ilike', "%{$this->search}%"))
            ->when($this->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->sort === 'name_asc', fn($q) => $q->orderBy('name', 'asc'))
            ->when($this->sort === 'name_desc', fn($q) => $q->orderBy('name', 'desc'))
            ->when($this->sort === 'days_high', fn($q) => $q->orderBy('days_per_year', 'desc'))
            ->when($this->sort === 'days_low', fn($q) => $q->orderBy('days_per_year', 'asc'));
    }

    public function render()
    {
        $leaveTypes = $this->getQuery()->paginate(15);

        return view('livewire.hr.leave.types.index', [
            'leaveTypes' => $leaveTypes,
        ]);
    }
}
