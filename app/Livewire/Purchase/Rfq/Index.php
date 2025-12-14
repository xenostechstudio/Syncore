<?php

namespace App\Livewire\Purchase\Rfq;

use App\Livewire\Concerns\WithManualPagination;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Request for Quotation')]
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
        'rfq' => true,
        'supplier' => true,
        'date' => true,
        'total' => true,
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
            $this->selected = $this->getRfqQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
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

    private function getRfqQuery()
    {
        // Mock query - replace with actual model when ready
        return DB::table('purchase_rfqs')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference', 'like', "%{$this->search}%")
                        ->orWhere('supplier_name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status !== 'all', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->sort === 'oldest', fn($q) => $q->orderBy('created_at', 'asc'))
            ->when($this->sort === 'reference', fn($q) => $q->orderBy('reference', 'asc'))
            ->when($this->sort === 'latest', fn($q) => $q->orderBy('created_at', 'desc'));
    }

    public function render()
    {
        $query = $this->getRfqQuery();
        $total = $query->count();
        
        $rfqs = $query
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        $this->totalPages = (int) ceil($total / $this->perPage);

        return view('livewire.purchase.rfq.index', [
            'rfqs' => $rfqs,
            'total' => $total,
        ]);
    }
}
