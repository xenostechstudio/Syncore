<?php

namespace App\Livewire\Purchase\Bills;

use App\Exports\VendorBillsExport;
use App\Imports\VendorBillsImport;
use App\Livewire\Concerns\WithImport;
use App\Models\Purchase\VendorBill;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Vendor Bills')]
class Index extends Component
{
    use WithPagination, WithImport;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getBillsQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
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
        $this->reset(['search', 'status']);
        $this->resetPage();
        $this->clearSelection();
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $bills = VendorBill::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($bills as $bill) {
            if ($bill->status === 'draft') {
                $canDelete[] = [
                    'id' => $bill->id,
                    'name' => $bill->bill_number,
                    'status' => $bill->status,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $bill->id,
                    'name' => $bill->bill_number,
                    'reason' => "Status is '{$bill->status}' - only draft bills can be deleted",
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

        $count = VendorBill::whereIn('id', $this->selected)
            ->where('status', 'draft')
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} bills deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = VendorBill::whereIn('id', $this->selected)->update(['status' => $status]);

        $this->clearSelection();
        session()->flash('success', "{$count} bills updated to {$status}.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new VendorBillsExport(), 'vendor-bills-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new VendorBillsExport($this->selected), 'vendor-bills-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getImportClass(): string
    {
        return VendorBillsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['supplier', 'vendor_reference', 'bill_date', 'due_date', 'status', 'subtotal', 'tax', 'total', 'notes'],
            'filename' => 'vendor-bills-template.csv',
        ];
    }

    protected function getBillsQuery()
    {
        return VendorBill::query()
            ->when($this->search, fn($q) => $q->where(function ($query) {
                $query->where('bill_number', 'ilike', "%{$this->search}%")
                    ->orWhere('vendor_reference', 'ilike', "%{$this->search}%")
                    ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'ilike', "%{$this->search}%"));
            }))
            ->when($this->status, fn($q) => $q->where('status', $this->status));
    }

    public function render()
    {
        $bills = $this->getBillsQuery()
            ->with(['supplier'])
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->paginate(15);

        return view('livewire.purchase.bills.index', [
            'bills' => $bills,
        ]);
    }
}
