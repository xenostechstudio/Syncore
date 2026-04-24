<?php

namespace App\Livewire\Purchase\Rfq;

use App\Exports\PurchaseOrdersExport;
use App\Imports\PurchaseRfqsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithIndexComponent;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Request for Quotation')]
class Index extends Component
{
    use WithIndexComponent, WithImport;

    #[Url]
    public int $perPage = 10;

    public string $viewType = 'list';

    public array $visibleColumns = [
        'rfq' => true,
        'supplier' => true,
        'date' => true,
        'total' => true,
        'status' => true,
    ];

    public function mount(): void
    {
        $this->status = 'all';
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = ! $this->visibleColumns[$column];
        }
    }

    public function setView(string $view): void
    {
        $this->viewType = $view;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'sort']);
        $this->status = 'all';
        $this->resetPage();
        $this->clearSelection();
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $rfqs = DB::table('purchase_rfqs')->whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($rfqs as $rfq) {
            if (in_array($rfq->status, ['draft', 'rfq'], true)) {
                $canDelete[] = ['id' => $rfq->id, 'name' => $rfq->reference, 'status' => $rfq->status];
            } else {
                $cannotDelete[] = [
                    'id' => $rfq->id,
                    'name' => $rfq->reference,
                    'reason' => "Status is '{$rfq->status}' - only draft/rfq can be deleted",
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

        $count = DB::table('purchase_rfqs')
            ->whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'rfq'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} RFQs deleted successfully.");
    }

    public function bulkConfirm(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = DB::table('purchase_rfqs')
            ->whereIn('id', $this->selected)
            ->where('status', 'rfq')
            ->update(['status' => 'purchase_order']);

        $this->clearSelection();
        session()->flash('success', "{$count} RFQs confirmed as purchase orders.");
    }

    public function bulkCancel(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = DB::table('purchase_rfqs')
            ->whereIn('id', $this->selected)
            ->whereNotIn('status', ['cancelled', 'received'])
            ->update(['status' => 'cancelled']);

        $this->clearSelection();
        session()->flash('success', "{$count} RFQs cancelled.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'rfqs-' . now()->format('Y-m-d') . '.xlsx'
            : 'rfqs-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new PurchaseOrdersExport($this->selected ?: null), $filename);
    }

    protected function getImportClass(): string
    {
        return PurchaseRfqsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['supplier', 'order_date', 'expected_arrival', 'status', 'subtotal', 'tax', 'total', 'notes'],
            'filename' => 'purchase-rfqs-template.csv',
        ];
    }

    protected function getQuery()
    {
        return DB::table('purchase_rfqs')
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhere('supplier_name', 'like', "%{$this->search}%")))
            ->when($this->status !== 'all' && $this->status !== '', fn ($q) => $q->where('status', $this->status));
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->orderBy('created_at', 'asc'),
            'reference' => $this->getQuery()->orderBy('reference', 'asc'),
            default => $this->getQuery()->orderBy('created_at', 'desc'),
        };

        $total = $query->count();
        $rfqs = $query
            ->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        $this->totalPages = (int) ceil($total / max(1, $this->perPage));

        return view('livewire.purchase.rfq.index', [
            'rfqs' => $rfqs,
            'total' => $total,
        ]);
    }
}
