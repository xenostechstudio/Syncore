<?php

namespace App\Livewire\Invoicing\Invoices;

use App\Models\Invoicing\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Invoices')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
    public string $view = 'list';
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

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function toggleColumn(string $column): void
    {
        $this->visibleColumns[$column] = !($this->visibleColumns[$column] ?? true);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
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

    public function render()
    {
        $invoices = Invoice::query()
            ->with(['customer', 'salesOrder'])
            ->when($this->search, fn($q) => $q->where('invoice_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Invoice::count(),
            'draft' => Invoice::where('status', 'draft')->count(),
            'sent' => Invoice::where('status', 'sent')->count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
            'totalAmount' => Invoice::sum('total'),
            'paidAmount' => Invoice::where('status', 'paid')->sum('total'),
        ];

        return view('livewire.invoicing.invoices.index', [
            'invoices' => $invoices,
            'stats' => $stats,
        ]);
    }
}
