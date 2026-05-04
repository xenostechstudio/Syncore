<?php

namespace App\Livewire\Sales\Configuration\Taxes;

use App\Exports\TaxesExport;
use App\Imports\TaxesImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithPermissions;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Sales\Tax;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Taxes')]
class Index extends Component
{
    use WithIndexComponent, WithImport, WithPermissions;

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $ids = ! empty($this->selected) ? $this->selected : null;

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
        $this->authorizePermission('sales.delete');

        if (empty($this->selected)) {
            return;
        }

        $count = Tax::whereIn('id', $this->selected)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} taxes deleted successfully.");
    }

    public function activateSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Tax::whereIn('id', $this->selected)->update(['is_active' => true]);
        $this->clearSelection();
        session()->flash('success', 'Selected taxes activated.');
    }

    public function deactivateSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        Tax::whereIn('id', $this->selected)->update(['is_active' => false]);
        $this->clearSelection();
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

    protected function getQuery()
    {
        return Tax::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")));
    }

    protected function getModelClass(): string
    {
        return Tax::class;
    }

    public function render()
    {
        $taxes = $this->getQuery()
            ->orderBy('name')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.sales.configuration.taxes.index', [
            'taxes' => $taxes,
        ]);
    }
}
