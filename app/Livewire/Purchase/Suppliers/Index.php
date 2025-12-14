<?php

namespace App\Livewire\Purchase\Suppliers;

use App\Livewire\Concerns\WithManualPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Suppliers')]
class Index extends Component
{
    use WithManualPagination;

    public string $search = '';
    public string $status = 'all';
    public string $sort = 'latest';
    public string $viewType = 'list';

    #[Url]
    public int $perPage = 10;

    public array $selected = [];
    public bool $selectAll = false;

    public array $visibleColumns = [
        'supplier' => true,
        'contact' => true,
        'email' => true,
        'status' => true,
    ];

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = !$this->visibleColumns[$column];
        }
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
            $this->selected = $this->getSuppliersQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
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
        $this->search = '';
        $this->status = 'all';
        $this->sort = 'latest';
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function setView(string $view): void
    {
        $this->viewType = $view;
    }

    private function getSuppliersQuery()
    {
        return DB::table('suppliers')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('contact_person', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->sort === 'oldest', fn($q) => $q->orderBy('created_at', 'asc'))
            ->when($this->sort === 'name', fn($q) => $q->orderBy('name', 'asc'))
            ->when($this->sort === 'latest', fn($q) => $q->orderBy('created_at', 'desc'));
    }

    public function render()
    {
        $query = $this->getSuppliersQuery();
        $total = $query->count();
        
        $suppliers = $query
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        $this->totalPages = (int) ceil($total / $this->perPage);

        return view('livewire.purchase.suppliers.index', [
            'suppliers' => $suppliers,
            'total' => $total,
        ]);
    }
}
