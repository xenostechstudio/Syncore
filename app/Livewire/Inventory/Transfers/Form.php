<?php

namespace App\Livewire\Inventory\Transfers;

use App\Models\Inventory\InventoryTransfer;
use App\Models\Inventory\InventoryTransferItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Transfer')]
class Form extends Component
{
    public ?int $transferId = null;
    public ?InventoryTransfer $transfer = null;
    public bool $editing = false;

    public ?int $source_warehouse_id = null;
    public ?int $destination_warehouse_id = null;
    public string $transfer_date = '';
    public ?string $expected_arrival_date = null;
    public string $status = 'draft';
    public string $notes = '';

    public array $items = [];
    public string $productSearch = '';

    public function mount(?int $id = null): void
    {
        $this->transfer_date = now()->format('Y-m-d');

        if ($id) {
            $this->transferId = $id;
            $this->editing = true;
            $this->loadTransfer();
        } else {
            $this->addItem();
        }
    }

    protected function loadTransfer(): void
    {
        $this->transfer = InventoryTransfer::with(['items.product'])->findOrFail($this->transferId);

        $this->source_warehouse_id = $this->transfer->source_warehouse_id;
        $this->destination_warehouse_id = $this->transfer->destination_warehouse_id;
        $this->transfer_date = $this->transfer->transfer_date->format('Y-m-d');
        $this->expected_arrival_date = $this->transfer->expected_arrival_date?->format('Y-m-d');
        $this->status = $this->transfer->status;
        $this->notes = $this->transfer->notes ?? '';

        $this->items = $this->transfer->items->map(fn($item) => [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'name' => $item->product->name ?? '',
            'sku' => $item->product->sku ?? '',
            'quantity' => $item->quantity,
            'received_quantity' => $item->received_quantity,
        ])->toArray();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'id' => null,
            'product_id' => null,
            'name' => '',
            'sku' => '',
            'quantity' => 1,
            'received_quantity' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function selectProduct(int $index, int $productId): void
    {
        $product = Product::find($productId);
        if ($product) {
            $this->items[$index]['product_id'] = $product->id;
            $this->items[$index]['name'] = $product->name;
            $this->items[$index]['sku'] = $product->sku;
        }
    }

    public function save(): void
    {
        $validItems = collect($this->items)->filter(fn($item) => !empty($item['product_id']))->values()->toArray();

        if (empty($validItems)) {
            $this->addError('items', 'Please add at least one product to the transfer.');
            return;
        }

        $this->items = $validItems;

        $this->validate([
            'source_warehouse_id' => 'required|exists:warehouses,id',
            'destination_warehouse_id' => 'required|exists:warehouses,id|different:source_warehouse_id',
            'transfer_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $data = [
            'source_warehouse_id' => $this->source_warehouse_id,
            'destination_warehouse_id' => $this->destination_warehouse_id,
            'user_id' => Auth::id(),
            'transfer_date' => $this->transfer_date,
            'expected_arrival_date' => $this->expected_arrival_date ?: null,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ];

        if ($this->transferId) {
            $transfer = InventoryTransfer::findOrFail($this->transferId);
            $transfer->update($data);
            $transfer->items()->delete();
        } else {
            $data['transfer_number'] = InventoryTransfer::generateTransferNumber();
            $transfer = InventoryTransfer::create($data);
            $this->transferId = $transfer->id;
        }

        foreach ($this->items as $item) {
            InventoryTransferItem::create([
                'inventory_transfer_id' => $transfer->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'received_quantity' => $item['received_quantity'] ?? 0,
            ]);
        }

        session()->flash('success', 'Transfer saved successfully.');
        $this->redirect(route('inventory.transfers.edit', $transfer->id), navigate: true);
    }

    public function render()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        $products = Product::query()
            ->when($this->productSearch, fn($q) => $q->where('name', 'like', "%{$this->productSearch}%")
                ->orWhere('sku', 'like', "%{$this->productSearch}%"))
            ->orderBy('name')
            ->limit(20)
            ->get();

        return view('livewire.inventory.transfers.form', [
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }
}
