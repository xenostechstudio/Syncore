<?php

namespace App\Livewire\Sales\Configuration\Taxes;

use App\Exports\TaxesExport;
use App\Imports\TaxesImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Sales\Tax;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Taxes')]
class Index extends Component
{
    use WithManualPagination, WithImport;

    #[Url]
    public string $search = '';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = Tax::query()
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

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $ids = !empty($this->selected) ? $this->selected : null;
        return Excel::download(new TaxesExport($ids), 'taxes-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $taxes = Tax::whereIn('id', $this->selected)->get();

        $canDelete = [];
        foreach ($taxes as $tax) {
            $canDelete[] = [
                'id' => $tax->id,
                'name' => $tax->name . ' (' . $tax->rate . '%)',
            ];
        }

        $this->deleteValidation = [
            'canDelete' => $canDelete,
            'cannotDelete' => [],
            'totalSelected' => count($this->selected),
        ];

        $this->showDeleteConfirm = true;
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Tax::whereIn('id', $this->selected)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} taxes deleted successfully.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function activateSelected(): void
    {
        Tax::whereIn('id', $this->selected)->update(['is_active' => true]);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected taxes activated.');
    }

    public function deactivateSelected(): void
    {
        Tax::whereIn('id', $this->selected)->update(['is_active' => false]);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('success', 'Selected taxes deactivated.');
    }

    protected function getImportClass(): string
    {
        return TaxesImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['name', 'code', 'rate', 'type', 'scope', 'include_in_price', 'is_active', 'description'],
            'filename' => 'taxes-template.csv',
        ];
    }

    public function render()
    {
        $taxes = Tax::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.sales.configuration.taxes.index', [
            'taxes' => $taxes,
        ]);
    }
}
