<?php

namespace App\Livewire\Inventory\Products\Pricelists;

use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Sales\Pricelist;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Pricelists')]
class Index extends Component
{
    use WithIndexComponent;

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Pricelist::whereIn('id', $this->selected)->delete();
        $this->clearSelection();
        session()->flash('success', 'Selected pricelists deleted successfully.');
    }

    protected function getQuery()
    {
        return Pricelist::query()
            ->withCount('items')
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")));
    }

    protected function getModelClass(): string
    {
        return Pricelist::class;
    }

    public function render()
    {
        $pricelists = $this->getQuery()
            ->orderBy('name')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.inventory.products.pricelists.index', [
            'pricelists' => $pricelists,
        ]);
    }
}
