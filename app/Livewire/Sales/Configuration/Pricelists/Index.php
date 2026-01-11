<?php

namespace App\Livewire\Sales\Configuration\Pricelists;

use App\Exports\PricelistsExport;
use App\Imports\PricelistsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Sales\Pricelist;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Pricelists')]
class Index extends Component
{
    use WithManualPagination, WithImport;

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
                ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%"))
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

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $ids = !empty($this->selected) ? $this->selected : null;
        return Excel::download(new PricelistsExport($ids), 'pricelists-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function deleteSelected(): void
    {
        Pricelist::whereIn('id', $this->selected)->delete();
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected pricelists deleted successfully.');
    }

    protected function getImportClass(): string
    {
        return PricelistsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['name', 'code', 'currency', 'type', 'discount', 'start_date', 'end_date', 'is_active', 'description'],
            'filename' => 'pricelists-template.csv',
        ];
    }

    public function render()
    {
        $pricelists = Pricelist::query()
            ->withCount('items')
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('code', 'ilike', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.sales.configuration.pricelists.index', [
            'pricelists' => $pricelists,
        ]);
    }
}
