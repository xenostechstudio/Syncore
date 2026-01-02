<?php

namespace App\Livewire\HR\Departments;

use App\Models\HR\Department;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Departments')]
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
    public string $view = 'grid';

    public array $selected = [];
    public bool $selectAll = false;

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
