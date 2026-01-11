<?php

namespace App\Livewire\Delivery\Orders;

use App\Exports\DeliveryOrdersExport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Delivery\DeliveryOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Delivery'])]
#[Title('Delivery Orders')]
class Index extends Component
{
    use WithManualPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];
    
    public array $visibleColumns = [
        'delivery_number' => true,
        'sales_order' => true,
        'recipient' => true,
        'courier' => true,
        'delivery_date' => true,
        'status' => true,
    ];

    public function setView(string $view): void
    {
        if (! in_array($view, ['list', 'grid'])) {
            return;
        }

        $this->view = $view;
    }

    public function toggleColumn(string $column): void
    {
        $this->visibleColumns[$column] = !($this->visibleColumns[$column] ?? true);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function deleteSelected(): void
    {
        $this->confirmBulkDelete();
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        // Validate which delivery orders can be deleted
        $deliveries = DeliveryOrder::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($deliveries as $delivery) {
            if (in_array($delivery->status, ['draft', 'pending'])) {
                $canDelete[] = [
                    'id' => $delivery->id,
                    'name' => $delivery->delivery_number,
                    'status' => $delivery->status,
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $delivery->id,
                    'name' => $delivery->delivery_number,
                    'reason' => "Status is '{$delivery->status}' - only draft/pending can be deleted",
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

        $count = DeliveryOrder::whereIn('id', $this->selected)
            ->whereIn('status', ['draft', 'pending'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} delivery orders deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function bulkMarkShipped(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = DeliveryOrder::whereIn('id', $this->selected)
            ->whereIn('status', ['pending', 'ready'])
            ->update(['status' => 'shipped', 'shipped_at' => now()]);

        $this->clearSelection();
        session()->flash('success', "{$count} delivery orders marked as shipped.");
    }

    public function bulkMarkDelivered(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = DeliveryOrder::whereIn('id', $this->selected)
            ->where('status', 'shipped')
            ->update(['status' => 'delivered', 'delivered_at' => now()]);

        $this->clearSelection();
        session()->flash('success', "{$count} delivery orders marked as delivered.");
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new DeliveryOrdersExport(), 'delivery-orders-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new DeliveryOrdersExport($this->selected), 'delivery-orders-selected-' . now()->format('Y-m-d') . '.xlsx');
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
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getDeliveriesQuery()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort']);
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    private function getDeliveriesQuery()
    {
        return DeliveryOrder::query()
            ->with(['salesOrder.customer', 'warehouse'])
            ->when($this->search, fn($q) => $q->where(fn ($qq) => $qq
                ->where('delivery_number', 'ilike', "%{$this->search}%")
                ->orWhere('tracking_number', 'ilike', "%{$this->search}%")
                ->orWhereHas('salesOrder.customer', fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'delivery_date', fn($q) => $q->orderBy('delivery_date'));
    }

    public function render()
    {
        $deliveries = $this->getDeliveriesQuery()->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.delivery.orders.index', [
            'deliveries' => $deliveries,
        ]);
    }
}
