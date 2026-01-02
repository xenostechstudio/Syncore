<?php

namespace App\Livewire\Reports\Sales;

use App\Services\Reports\SalesReportService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Reports'])]
#[Title('Sales Reports')]
class Index extends Component
{
    public string $period = 'this_month';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public string $groupBy = 'day';
    public string $reportType = 'overview';

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
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
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

        $service = app(SalesReportService::class);

        return view('livewire.reports.sales.index', [
            'summary' => $service->getSummary($startDate, $endDate),
            'salesByPeriod' => $service->getSalesByPeriod($startDate, $endDate, $this->groupBy),
            'salesByCustomer' => $service->getSalesByCustomer($startDate, $endDate, 10),
            'salesByProduct' => $service->getSalesByProduct($startDate, $endDate, 10),
            'salespersonPerformance' => $service->getSalespersonPerformance($startDate, $endDate),
        ]);
    }
}
