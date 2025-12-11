<?php

namespace App\Livewire\Sales\Configuration\Pricelists;

use App\Models\Sales\Pricelist;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Pricelists')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public array $selected = [];
    public bool $selectAll = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = Pricelist::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
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

    public function deleteSelected(): void
    {
        Pricelist::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected pricelists deleted successfully.');
    }

    public function render()
    {
        $pricelists = Pricelist::query()
            ->withCount('items')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.sales.configuration.pricelists.index', [
            'pricelists' => $pricelists,
        ]);
    }
}
