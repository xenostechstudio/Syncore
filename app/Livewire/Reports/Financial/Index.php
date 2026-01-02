<?php

namespace App\Livewire\Reports\Financial;

use App\Services\Reports\InvoiceReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Reports'])]
#[Title('Financial Reports')]
class Index extends Component
{
    public string $period = 'this_month';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public string $groupBy = 'month';

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
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
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

        $service = app(InvoiceReportService::class);

        return view('livewire.reports.financial.index', [
            'summary' => $service->getSummary($startDate, $endDate),
            'revenueByPeriod' => $service->getRevenueByPeriod($startDate, $endDate, $this->groupBy),
            'agingReport' => $service->getAgingReport(),
            'paymentsByMethod' => $service->getPaymentsByMethod($startDate, $endDate),
        ]);
    }
}
