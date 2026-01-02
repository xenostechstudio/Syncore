<?php

namespace App\Livewire\Reports;

use App\Services\Reports\SalesReportService;
use App\Services\Reports\InventoryReportService;
use App\Services\Reports\InvoiceReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Reports'])]
#[Title('Reports')]
class Index extends Component
{
    public string $period = 'this_month';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->setPeriodDates();
    }

    public function updatedPeriod(): void
    {
        $this->setPeriodDates();
    }

    protected function setPeriodDates(): void
    {
        [$start, $end] = match ($this->period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                $this->startDate ? Carbon::parse($this->startDate) : now()->startOfMonth(),
                $this->endDate ? Carbon::parse($this->endDate) : now()->endOfMonth(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');
    }

    public function render()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $salesService = app(SalesReportService::class);
        $inventoryService = app(InventoryReportService::class);
        $invoiceService = app(InvoiceReportService::class);

        return view('livewire.reports.index', [
            'salesSummary' => $salesService->getSummary($startDate, $endDate),
            'inventorySummary' => $inventoryService->getSummary(),
            'invoiceSummary' => $invoiceService->getSummary($startDate, $endDate),
            'topProducts' => $salesService->getSalesByProduct($startDate, $endDate, 5),
            'topCustomers' => $salesService->getSalesByCustomer($startDate, $endDate, 5),
        ]);
    }
}
