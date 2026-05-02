<?php

namespace App\Livewire\Purchase\Receipts;

use App\Enums\PurchaseReceiptState;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Purchase\PurchaseReceipt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Goods Receipts')]
class Index extends Component
{
    use WithIndexComponent;

    #[Url]
    public int $perPage = 10;

    public function mount(): void
    {
        $this->status = $this->status ?: 'all';
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function getAllowedViews(): array
    {
        return ['list'];
    }

    protected function getQuery()
    {
        $query = PurchaseReceipt::query()
            ->with(['supplier', 'warehouse', 'purchaseRfq:id,reference', 'receiver:id,name']);

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', $term)
                    ->orWhere('notes', 'like', $term)
                    ->orWhereHas('purchaseRfq', fn ($r) => $r->where('reference', 'like', $term))
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', $term));
            });
        }

        if ($this->status !== '' && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $this->applySorting($query);
    }

    protected function getModelClass(): string
    {
        return PurchaseReceipt::class;
    }

    public function render()
    {
        $receipts = $this->getQuery()->paginate($this->perPage);

        return view('livewire.purchase.receipts.index', [
            'receipts' => $receipts,
            'statusOptions' => PurchaseReceiptState::cases(),
        ]);
    }
}
