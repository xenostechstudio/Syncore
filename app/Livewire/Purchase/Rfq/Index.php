<?php

namespace App\Livewire\Purchase\Rfq;

use App\Exports\PurchaseOrdersExport;
use App\Imports\PurchaseRfqsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithManualPagination;
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
    use WithManualPagination, WithImport;

    public string $search = '';
    public string $status = 'all';
    public string $sort = 'latest';
    public string $viewType = 'list';

    #[Url]
    public int $perPage = 10;

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

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

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Validate which RFQs can be deleted
        $rfqs = DB::table('purchase_rfqs')
            ->whereIn('id', $this->selected)
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($rfqs as $rfq) {
            if (in_array($rfq->status, ['draft', 'rfq'])) {
                $canDelete[] = [
                    'id' => $rfq->id,
                    'name' => $rfq->reference,
                    'status' => $rfq->status,
                ];
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

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
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
        if (empty($this->selected)) {
            return Excel::download(new PurchaseOrdersExport(), 'rfqs-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new PurchaseOrdersExport($this->selected), 'rfqs-selected-' . now()->format('Y-m-d') . '.xlsx');
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

    private function getRfqQuery()
    {
        // Mock query - replace with actual model when ready
        return DB::table('purchase_rfqs')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference', 'ilike', "%{$this->search}%")
                        ->orWhere('supplier_name', 'ilike', "%{$this->search}%");
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
