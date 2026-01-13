<?php

namespace App\Livewire\Invoicing;

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use App\Models\Sales\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Invoicing'])]
#[Title('Invoicing Overview')]
class Index extends Component
{
    public function render()
    {
        // Main Stats
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('total');
        $totalPaid = Invoice::where('status', 'paid')->sum('paid_amount');
        
        // This month stats
        $invoicesThisMonth = Invoice::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $revenueThisMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_date', now()->month)
            ->whereYear('paid_date', now()->year)
            ->sum('total');
        
        // Last month stats for comparison
        $invoicesLastMonth = Invoice::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $revenueLastMonth = Invoice::where('status', 'paid')
            ->whereMonth('paid_date', now()->subMonth()->month)
            ->whereYear('paid_date', now()->subMonth()->year)
            ->sum('total');

        // Invoice status counts
        $draftInvoices = Invoice::where('status', 'draft')->count();
        $sentInvoices = Invoice::where('status', 'sent')->count();
        $partialInvoices = Invoice::where('status', 'partial')->count();
        $paidInvoices = Invoice::where('status', 'paid')->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $cancelledInvoices = Invoice::where('status', 'cancelled')->count();

        // Amounts
        $awaitingPayment = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->sum('total');
        $overdueAmount = Invoice::where('status', 'overdue')->sum('total');
        $partialAmount = Invoice::where('status', 'partial')->sum(DB::raw('total - paid_amount'));

        // Average invoice value
        $avgInvoiceValue = $totalInvoices > 0 ? $totalRevenue / max($paidInvoices, 1) : 0;
        $avgInvoiceValueThisMonth = $invoicesThisMonth > 0 ? $revenueThisMonth / max($invoicesThisMonth, 1) : 0;

        // Monthly revenue data for chart (last 6 months)
        $monthlyRevenue = Invoice::where('status', 'paid')
            ->where('paid_date', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("EXTRACT(YEAR FROM paid_date) as year"),
                DB::raw("EXTRACT(MONTH FROM paid_date) as month"),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as invoices')
            )
            ->groupBy(DB::raw("EXTRACT(YEAR FROM paid_date)"), DB::raw("EXTRACT(MONTH FROM paid_date)"))
            ->orderBy(DB::raw("EXTRACT(YEAR FROM paid_date)"))
            ->orderBy(DB::raw("EXTRACT(MONTH FROM paid_date)"))
            ->get()
            ->map(function ($item) {
                return [
                    'month' => date('M', mktime(0, 0, 0, (int) $item->month, 1)),
                    'revenue' => $item->revenue,
                    'invoices' => $item->invoices,
                ];
            });

        // Recent invoices
        $recentInvoices = Invoice::with('customer')
            ->latest()
            ->take(5)
            ->get();

        // Recent payments
        $recentPayments = Payment::with(['invoice.customer'])
            ->latest()
            ->take(5)
            ->get();

        // Top customers by invoice amount
        $topCustomers = Customer::withCount('invoices')
            ->withSum('invoices', 'total')
            ->orderByDesc('invoices_sum_total')
            ->take(5)
            ->get();

        // Collection rate
        $totalBilled = Invoice::whereIn('status', ['sent', 'partial', 'paid', 'overdue'])->sum('total');
        $collectionRate = $totalBilled > 0 ? ($totalPaid / $totalBilled) * 100 : 0;

        return view('livewire.invoicing.index', [
            'totalInvoices' => $totalInvoices,
            'totalRevenue' => $totalRevenue,
            'totalPaid' => $totalPaid,
            'invoicesThisMonth' => $invoicesThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'invoicesLastMonth' => $invoicesLastMonth,
            'revenueLastMonth' => $revenueLastMonth,
            'draftInvoices' => $draftInvoices,
            'sentInvoices' => $sentInvoices,
            'partialInvoices' => $partialInvoices,
            'paidInvoices' => $paidInvoices,
            'overdueInvoices' => $overdueInvoices,
            'cancelledInvoices' => $cancelledInvoices,
            'awaitingPayment' => $awaitingPayment,
            'overdueAmount' => $overdueAmount,
            'partialAmount' => $partialAmount,
            'avgInvoiceValue' => $avgInvoiceValue,
            'avgInvoiceValueThisMonth' => $avgInvoiceValueThisMonth,
            'monthlyRevenue' => $monthlyRevenue,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
            'topCustomers' => $topCustomers,
            'collectionRate' => $collectionRate,
        ]);
    }
}
