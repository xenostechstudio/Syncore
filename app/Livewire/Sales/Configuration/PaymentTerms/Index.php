<?php

namespace App\Livewire\Sales\Configuration\PaymentTerms;

use App\Exports\PaymentTermsExport;
use App\Imports\PaymentTermsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Sales\PaymentTerm;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Payment Terms')]
class Index extends Component
{
    use WithIndexComponent, WithImport;

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $ids = ! empty($this->selected) ? $this->selected : null;

        return Excel::download(new PaymentTermsExport($ids), 'payment-terms-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        PaymentTerm::whereIn('id', $this->selected)->delete();
        $this->clearSelection();
        session()->flash('success', 'Selected payment terms deleted successfully.');
    }

    public function activateSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        PaymentTerm::whereIn('id', $this->selected)->update(['is_active' => true]);
        $this->clearSelection();
        session()->flash('success', 'Selected payment terms activated.');
    }

    public function deactivateSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        PaymentTerm::whereIn('id', $this->selected)->update(['is_active' => false]);
        $this->clearSelection();
        session()->flash('success', 'Selected payment terms deactivated.');
    }

    protected function getImportClass(): string
    {
        return PaymentTermsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['name', 'code', 'days', 'description', 'is_active', 'sort_order'],
            'filename' => 'payment-terms-template.csv',
        ];
    }

    protected function getQuery()
    {
        return PaymentTerm::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")));
    }

    protected function getModelClass(): string
    {
        return PaymentTerm::class;
    }

    public function render()
    {
        $paymentTerms = $this->getQuery()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.sales.configuration.payment-terms.index', [
            'paymentTerms' => $paymentTerms,
        ]);
    }
}
