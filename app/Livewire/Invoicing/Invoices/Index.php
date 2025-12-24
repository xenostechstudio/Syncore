<?php

namespace App\Livewire\Invoicing\Invoices;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Invoicing\Invoice;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Invoices')]
class Index extends Component
{
    use WithManualPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
    #[Url]
    public string $view = 'list';

    #[Url]
    public bool $showStats = true;
    
    public array $selected = [];
    public bool $selectAll = false;
    
    public array $visibleColumns = [
        'invoice_number' => true,
        'customer' => true,
        'invoice_date' => true,
        'due_date' => true,
        'total' => true,
        'status' => true,
    ];

    public function toggleStats(): void
    {
        $this->showStats = !$this->showStats;
    }

    public function setView(string $view): void
    {
        if (! in_array($view, ['list', 'grid'], true)) {
            return;
        }

        $this->view = $view;
    }

    public function toggleColumn(string $column): void
    {
        $this->visibleColumns[$column] = !($this->visibleColumns[$column] ?? true);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function clearSelection(): void
    {
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
            $this->selected = $this->getInvoicesQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function toggleSelectAll(): void
    {
        $this->selectAll = !$this->selectAll;
    }

    public function deleteSelected(): void
    {
        if (count($this->selected) > 0) {
            Invoice::whereIn('id', $this->selected)->delete();
            $this->selected = [];
            $this->selectAll = false;
            session()->flash('success', 'Selected invoices deleted successfully.');
        }
    }

    private function getInvoicesQuery()
    {
        return Invoice::query()
            ->with(['customer', 'salesOrder'])
            ->when($this->search, fn($q) => $q->where(fn ($qq) => $qq
                ->where('invoice_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest();
    }

    private function getStatistics(): array
    {
        $stats = Invoice::query()
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'total' => $stats->sum('count'),
            'total_amount' => $stats->sum('total'),
            'draft' => $stats->get('draft')?->count ?? 0,
            'sent' => $stats->get('sent')?->count ?? 0,
            'partial' => $stats->get('partial')?->count ?? 0,
            'paid' => $stats->get('paid')?->count ?? 0,
            'paid_amount' => $stats->get('paid')?->total ?? 0,
            'overdue' => $stats->get('overdue')?->count ?? 0,
            'overdue_amount' => $stats->get('overdue')?->total ?? 0,
        ];
    }

    public function render()
    {
        $invoices = $this->getInvoicesQuery()->paginate(15, ['*'], 'page', $this->page);
        $this->totalPages = $invoices->lastPage();

        return view('livewire.invoicing.invoices.index', [
            'invoices' => $invoices,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
