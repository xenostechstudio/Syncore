<?php

namespace App\Livewire\Invoicing\Invoices;

use App\Exports\InvoicesExport;
use App\Imports\InvoicesImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithPermissions;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Invoicing\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Invoices')]
class Index extends Component
{
    use WithIndexComponent, WithImport, WithPermissions;

    #[Url]
    public bool $myInvoice = true;

    public array $visibleColumns = [
        'invoice_number' => true,
        'customer' => true,
        'salesperson' => true,
        'invoice_date' => true,
        'due_date' => true,
        'total' => true,
        'status' => true,
    ];

    public function toggleColumn(string $column): void
    {
        $this->visibleColumns[$column] = ! ($this->visibleColumns[$column] ?? true);
    }

    public function updatedMyInvoice(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy']);
        $this->myInvoice = true;
        $this->resetPage();
        $this->clearSelection();
    }

    protected function getCustomActiveFilterCount(): int
    {
        return $this->myInvoice ? 0 : 1;
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $invoices = Invoice::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($invoices as $invoice) {
            $statusValue = $invoice->status?->value ?? $invoice->status;
            if ($statusValue === 'draft') {
                $canDelete[] = [
                    'id' => $invoice->id,
                    'name' => $invoice->invoice_number,
                    'status' => $statusValue,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $invoice->id,
                    'name' => $invoice->invoice_number,
                    'reason' => "Status is '{$statusValue}' - only draft invoices can be deleted",
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
        $this->authorizePermission('invoicing.delete');

        if (empty($this->selected)) {
            return;
        }

        $count = Invoice::whereIn('id', $this->selected)
            ->where('status', 'draft')
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} invoices deleted successfully.");
    }

    public function bulkMarkSent(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Invoice::whereIn('id', $this->selected)
            ->where('status', 'draft')
            ->update(['status' => 'sent']);

        $this->clearSelection();
        session()->flash('success', "{$count} invoices marked as sent.");
    }

    public function bulkMarkPaid(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Invoice::whereIn('id', $this->selected)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->update(['status' => 'paid', 'paid_date' => now()]);

        $this->clearSelection();
        session()->flash('success', "{$count} invoices marked as paid.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'invoices-' . now()->format('Y-m-d') . '.xlsx'
            : 'invoices-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new InvoicesExport($this->selected ?: null), $filename);
    }

    protected function getImportClass(): string
    {
        return InvoicesImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['customer', 'invoice_date', 'due_date', 'status', 'subtotal', 'tax', 'discount', 'total', 'notes', 'terms'],
            'filename' => 'invoices-template.csv',
        ];
    }

    protected function getStatistics(): array
    {
        $baseQuery = Invoice::query()
            ->when($this->myInvoice, fn ($q) => $q->where('user_id', Auth::id()));

        $stats = (clone $baseQuery)
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

    protected function getQuery()
    {
        return Invoice::query()
            ->with(['customer', 'salesOrder', 'user'])
            ->when($this->myInvoice, fn ($q) => $q->where('user_id', Auth::id()))
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('invoice_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$this->search}%"))))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return Invoice::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->oldest(),
            'total_high' => $this->getQuery()->orderByDesc('total'),
            'total_low' => $this->getQuery()->orderBy('total'),
            'due_date' => $this->getQuery()->orderBy('due_date'),
            default => $this->getQuery()->latest(),
        };

        $invoices = $query->paginate(15, ['*'], 'page', $this->page);
        $this->totalPages = $invoices->lastPage();

        return view('livewire.invoicing.invoices.index', [
            'invoices' => $invoices,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
