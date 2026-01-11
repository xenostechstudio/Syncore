<?php

namespace App\Livewire\Invoicing\Payments;

use App\Exports\PaymentsExport;
use App\Imports\PaymentsImport;
use App\Livewire\Concerns\WithImport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\Invoicing\Payment;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Payments')]
class Index extends Component
{
    use WithManualPagination, WithImport;

    #[Url]
    public string $search = '';

    #[Url]
    public string $view = 'list';

    #[Url]
    public bool $showStats = true;

    public array $selected = [];
    public bool $selectAll = false;

    public function toggleStats(): void
    {
        $this->showStats = !$this->showStats;
    }

    public function setView(string $view): void
    {
        if (! in_array($view, ['list'], true)) {
            return;
        }

        $this->view = $view;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = Payment::query()
                ->when($this->search, fn($q) => $q->where('reference', 'ilike', "%{$this->search}%"))
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $ids = !empty($this->selected) ? $this->selected : null;
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

    private function getStatistics(): array
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

    public function render()
    {
        $payments = Payment::query()
            ->with(['invoice.customer'])
            ->when($this->search, fn($q) => $q->where(fn ($qq) => $qq
                ->where('reference', 'ilike', "%{$this->search}%")
                ->orWhereHas('invoice', fn($q) => $q->where('invoice_number', 'ilike', "%{$this->search}%"))
            ))
            ->latest()
            ->paginate(15, ['*'], 'page', $this->page);

        $this->totalPages = $payments->lastPage();

        return view('livewire.invoicing.payments.index', [
            'payments' => $payments,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
