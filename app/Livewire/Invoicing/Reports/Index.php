<?php

namespace App\Livewire\Invoicing\Reports;

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use App\Models\Sales\SalesOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Carbon\Carbon;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Reports')]
class Index extends Component
{
    public string $period = 'this_month';
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount()
    {
        $this->setPeriodDates();
    }

    public function updatedPeriod()
    {
        $this->setPeriodDates();
    }

    protected function setPeriodDates()
    {
        $now = Carbon::now();
        
        match ($this->period) {
            'today' => [$this->startDate, $this->endDate] = [$now->toDateString(), $now->toDateString()],
            'this_week' => [$this->startDate, $this->endDate] = [$now->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'this_month' => [$this->startDate, $this->endDate] = [$now->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'last_month' => [$this->startDate, $this->endDate] = [$now->subMonth()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
            'this_quarter' => [$this->startDate, $this->endDate] = [$now->startOfQuarter()->toDateString(), $now->copy()->endOfQuarter()->toDateString()],
            'this_year' => [$this->startDate, $this->endDate] = [$now->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            default => null,
        };
    }

    public function render()
    {
        $invoices = Invoice::whereBetween('invoice_date', [$this->startDate, $this->endDate]);
        $payments = Payment::whereBetween('payment_date', [$this->startDate, $this->endDate]);
        $orders = SalesOrder::whereBetween('order_date', [$this->startDate, $this->endDate]);

        // Revenue metrics
        $totalRevenue = $invoices->clone()->where('status', 'paid')->sum('total');
        $totalInvoiced = $invoices->clone()->sum('total');
        $totalPaid = $payments->clone()->sum('amount');
        $totalOutstanding = $invoices->clone()->whereIn('status', ['sent', 'overdue'])->sum('total');
        
        // Invoice counts
        $invoiceCount = $invoices->clone()->count();
        $paidInvoices = $invoices->clone()->where('status', 'paid')->count();
        $pendingInvoices = $invoices->clone()->whereIn('status', ['draft', 'sent'])->count();
        $overdueInvoices = $invoices->clone()->where('status', 'overdue')->count();
        
        // Order metrics
        $orderCount = $orders->clone()->count();
        $orderTotal = $orders->clone()->sum('total');
        
        // Monthly breakdown for chart
        $monthlyData = $this->getMonthlyData();
        
        // Top customers by revenue
        $topCustomers = Invoice::selectRaw('customer_id, SUM(total) as total_revenue')
            ->whereBetween('invoice_date', [$this->startDate, $this->endDate])
            ->where('status', 'paid')
            ->groupBy('customer_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->with('customer')
            ->get();

        return view('livewire.invoicing.reports.index', [
            'totalRevenue' => $totalRevenue,
            'totalInvoiced' => $totalInvoiced,
            'totalPaid' => $totalPaid,
            'totalOutstanding' => $totalOutstanding,
            'invoiceCount' => $invoiceCount,
            'paidInvoices' => $paidInvoices,
            'pendingInvoices' => $pendingInvoices,
            'overdueInvoices' => $overdueInvoices,
            'orderCount' => $orderCount,
            'orderTotal' => $orderTotal,
            'monthlyData' => $monthlyData,
            'topCustomers' => $topCustomers,
        ]);
    }

    protected function getMonthlyData(): array
    {
        $data = [];
        $start = Carbon::parse($this->startDate)->startOfMonth();
        $end = Carbon::parse($this->endDate)->endOfMonth();
        
        while ($start <= $end) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();
            
            $revenue = Invoice::whereBetween('invoice_date', [$monthStart, $monthEnd])
                ->where('status', 'paid')
                ->sum('total');
            
            $data[] = [
                'month' => $start->format('M Y'),
                'revenue' => $revenue,
            ];
            
            $start->addMonth();
        }
        
        return $data;
    }
}
