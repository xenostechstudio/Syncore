<?php

namespace App\Livewire\Purchase\Bills;

use App\Exports\VendorBillsExport;
use App\Imports\VendorBillsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Purchase\VendorBill;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Vendor Bills')]
class Index extends Component
{
    use WithIndexComponent, WithImport;

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $bills = VendorBill::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($bills as $bill) {
            $statusValue = $bill->status?->value ?? $bill->status;
            if ($statusValue === 'draft') {
                $canDelete[] = [
                    'id' => $bill->id,
                    'name' => $bill->bill_number,
                    'status' => $statusValue,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $bill->id,
                    'name' => $bill->bill_number,
                    'reason' => "Status is '{$statusValue}' - only draft bills can be deleted",
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
        $filename = empty($this->selected)
            ? 'vendor-bills-' . now()->format('Y-m-d') . '.xlsx'
            : 'vendor-bills-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new VendorBillsExport($this->selected ?: null), $filename);
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

    protected function getQuery()
    {
        return VendorBill::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('bill_number', 'like', "%{$this->search}%")
                ->orWhere('vendor_reference', 'like', "%{$this->search}%")
                ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$this->search}%"))))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return VendorBill::class;
    }

    public function render()
    {
        $bills = $this->getQuery()
            ->with(['supplier'])
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.purchase.bills.index', [
            'bills' => $bills,
        ]);
    }
}
