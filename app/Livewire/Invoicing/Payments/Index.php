<?php

namespace App\Livewire\Invoicing\Payments;

use App\Exports\PaymentsExport;
use App\Imports\PaymentsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Invoicing\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Payments')]
class Index extends Component
{
    use WithIndexComponent, WithImport;

    public function mount(): void
    {
        $this->showStats = true;
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $ids = ! empty($this->selected) ? $this->selected : null;

        return Excel::download(new PaymentsExport($ids), 'payments-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getImportClass(): string
    {
        return PaymentsImport::class;
    }

    protected function getImportTemplate(): array
    {
        return [
            'headers' => ['invoice_number', 'payment_date', 'amount', 'payment_method', 'reference', 'notes', 'status'],
            'filename' => 'payments-template.csv',
        ];
    }

    protected function getStatistics(): array
    {
        $stats = Payment::query()
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(DISTINCT invoice_id) as invoices_count')
            )
            ->first();

        $methodStats = Payment::query()
            ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        return [
            'total_count' => $stats->total_count ?? 0,
            'total_amount' => $stats->total_amount ?? 0,
            'invoices_count' => $stats->invoices_count ?? 0,
            'bank_transfer' => $methodStats->get('bank_transfer')?->count ?? 0,
            'bank_transfer_amount' => $methodStats->get('bank_transfer')?->total ?? 0,
            'cash' => $methodStats->get('cash')?->count ?? 0,
            'cash_amount' => $methodStats->get('cash')?->total ?? 0,
        ];
    }

    protected function getQuery()
    {
        return Payment::query()
            ->with(['invoice.customer'])
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('reference', 'like', "%{$this->search}%")
                ->orWhereHas('invoice', fn ($iq) => $iq->where('invoice_number', 'like', "%{$this->search}%"))));
    }

    protected function getModelClass(): string
    {
        return Payment::class;
    }

    public function render()
    {
        $payments = $this->getQuery()
            ->latest()
            ->paginate(15, ['*'], 'page', $this->page);

        $this->totalPages = $payments->lastPage();

        return view('livewire.invoicing.payments.index', [
            'payments' => $payments,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
