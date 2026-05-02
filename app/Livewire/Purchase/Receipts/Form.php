<?php

namespace App\Livewire\Purchase\Receipts;

use App\Enums\PurchaseReceiptState;
use App\Livewire\Concerns\WithNotes;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\Purchase\PurchaseRfq;
use App\Services\PurchaseReceiptService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Goods Receipt')]
class Form extends Component
{
    use WithNotes;

    #[Locked]
    public ?int $receiptId = null;

    #[Locked]
    public ?int $rfqId = null;

    #[Url(as: 'rfq')]
    public ?int $rfqQueryId = null;

    public ?int $warehouseId = null;
    public string $reference = '';
    public string $status = 'draft';
    public ?string $receivedAt = null;
    public ?string $notes = '';

    public array $lines = [];

    public ?string $rfqReference = null;
    public ?string $supplierName = null;

    protected function getNotableModel()
    {
        return $this->receiptId ? PurchaseReceipt::find($this->receiptId) : null;
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->loadFromExisting($id);
            return;
        }

        $rfqId = $this->rfqQueryId ?? request()->query('rfq');

        if (! $rfqId) {
            session()->flash('error', 'A purchase order is required to create a receipt.');
            $this->redirect(route('purchase.orders.index'), navigate: true);
            return;
        }

        $rfq = PurchaseRfq::with(['items.product', 'supplier'])->findOrFail($rfqId);

        if (! $rfq->state->canReceive()) {
            session()->flash('error', 'This purchase order is not in a receivable state.');
            $this->redirect(route('purchase.orders.edit', $rfq->id), navigate: true);
            return;
        }

        $defaultWarehouse = Warehouse::orderBy('id')->first();

        $receipt = app(PurchaseReceiptService::class)
            ->buildDraftFor($rfq, $defaultWarehouse?->id ?? 0);

        $this->redirect(route('purchase.receipts.edit', $receipt->id), navigate: true);
    }

    public function save(): void
    {
        $receipt = $this->guardEditableReceipt();
        if (! $receipt) {
            return;
        }

        $this->validate([
            'warehouseId' => 'required|exists:warehouses,id',
            'lines.*.quantity_received' => 'required|numeric|min:0',
        ]);

        $receipt->update([
            'warehouse_id' => $this->warehouseId,
            'notes' => $this->notes ?: null,
        ]);

        foreach ($this->lines as $line) {
            $receipt->items()
                ->where('id', $line['id'])
                ->update(['quantity_received' => (float) $line['quantity_received']]);
        }

        session()->flash('success', 'Receipt saved.');
        $this->loadFromExisting($receipt->id);
    }

    public function validateReceipt(PurchaseReceiptService $service): void
    {
        $receipt = $this->guardEditableReceipt();
        if (! $receipt) {
            return;
        }

        $this->save();
        $receipt->refresh()->loadMissing(['items.rfqItem', 'purchaseRfq.items']);

        try {
            $ok = $service->validate($receipt);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        if (! $ok) {
            session()->flash('error', 'Receipt could not be validated.');
            return;
        }

        session()->flash('success', 'Receipt validated. Stock has been updated.');
        $this->loadFromExisting($receipt->id);
    }

    public function cancelReceipt(PurchaseReceiptService $service): void
    {
        if (! $this->receiptId) {
            return;
        }

        $receipt = PurchaseReceipt::find($this->receiptId);
        if (! $receipt) {
            return;
        }

        if (! $service->cancel($receipt)) {
            session()->flash('error', 'This receipt cannot be cancelled.');
            return;
        }

        session()->flash('success', 'Receipt cancelled.');
        $this->loadFromExisting($receipt->id);
    }

    private function guardEditableReceipt(): ?PurchaseReceipt
    {
        if (! $this->receiptId) {
            return null;
        }

        $receipt = PurchaseReceipt::find($this->receiptId);
        if (! $receipt) {
            return null;
        }

        if (! $receipt->state->canEdit()) {
            session()->flash('error', 'Only draft receipts can be edited.');
            return null;
        }

        return $receipt;
    }

    private function loadFromExisting(int $id): void
    {
        $receipt = PurchaseReceipt::with([
            'items.rfqItem.product',
            'items.product',
            'purchaseRfq.supplier',
            'warehouse',
        ])->findOrFail($id);

        $this->receiptId = $receipt->id;
        $this->rfqId = $receipt->purchase_rfq_id;
        $this->warehouseId = $receipt->warehouse_id;
        $this->reference = $receipt->reference;
        $this->status = $receipt->status;
        $this->receivedAt = $receipt->received_at?->format('Y-m-d');
        $this->notes = $receipt->notes;
        $this->rfqReference = $receipt->purchaseRfq?->reference;
        $this->supplierName = $receipt->purchaseRfq?->supplier?->name;

        $this->lines = $receipt->items->map(function ($item) {
            $rfqItem = $item->rfqItem;
            $ordered = $rfqItem ? (float) $rfqItem->quantity : 0;
            $alreadyReceived = $rfqItem ? (float) $rfqItem->quantity_received : 0;

            return [
                'id' => $item->id,
                'product_name' => $item->product?->name ?? $rfqItem?->product?->name ?? '—',
                'product_sku' => $item->product?->sku ?? $rfqItem?->product?->sku,
                'quantity_ordered' => $ordered,
                'quantity_already_received' => $alreadyReceived,
                'quantity_received' => (float) $item->quantity_received,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.purchase.receipts.form', [
            'warehouses' => Warehouse::orderBy('name')->get(['id', 'name']),
            'activities' => $this->receiptId ? $this->activitiesAndNotes : collect(),
            'receiptState' => PurchaseReceiptState::tryFrom($this->status) ?? PurchaseReceiptState::DRAFT,
        ]);
    }
}
