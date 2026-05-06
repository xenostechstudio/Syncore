<?php

namespace App\Livewire\Sales;

use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Overview')]
class Index extends Component
{
    public function render()
    {
        // Main Stats
        $totalOrders = SalesOrder::count();
        $totalCustomers = Customer::count();
        $totalRevenue = SalesOrder::where('status', 'delivered')->sum('total');
        
        // This month stats
        $ordersThisMonth = SalesOrder::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $revenueThisMonth = SalesOrder::where('status', 'delivered')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');
        
        // Last month stats for comparison
        $ordersLastMonth = SalesOrder::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $revenueLastMonth = SalesOrder::where('status', 'delivered')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total');
        
        // SalesOrder status distribution — single grouped scan, was 4
        // separate WHERE...COUNT queries.
        $ordersByStatus = SalesOrder::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        $quotations = (int) (($ordersByStatus['draft'] ?? 0)
            + ($ordersByStatus['confirmed'] ?? 0)
            + ($ordersByStatus['quotation'] ?? 0));
        $salesOrders = (int) ($ordersByStatus['sales_order'] ?? 0);
        $cancelledOrders = (int) ($ordersByStatus['cancelled'] ?? 0);
        $completedOrders = (int) ($ordersByStatus['delivered'] ?? 0);

        // To Invoice & To Deliver — these need item-level filters, so
        // they stay as separate queries.
        $toInvoice = SalesOrder::where('status', 'sales_order')
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_invoiced'))
            ->count();
        $toDeliver = SalesOrder::where('status', 'sales_order')
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_delivered'))
            ->count();

        // Invoice status distribution — single grouped scan with both
        // count and sum(total) so awaitingPayment piggybacks. Was 5
        // separate WHERE...COUNT queries plus a separate sum.
        $invoicesByStatus = Invoice::query()
            ->selectRaw('status, COUNT(*) as count, SUM(total) as total_sum')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        $overdueInvoices = (int) ($invoicesByStatus->get('overdue')?->count ?? 0);
        $paidInvoices = (int) ($invoicesByStatus->get('paid')?->count ?? 0);
        $draftInvoices = (int) ($invoicesByStatus->get('draft')?->count ?? 0);
        $sentInvoices = (int) ($invoicesByStatus->get('sent')?->count ?? 0);
        $partialInvoices = (int) ($invoicesByStatus->get('partial')?->count ?? 0);
        $awaitingPayment = (float) (($invoicesByStatus->get('sent')?->total_sum ?? 0)
            + ($invoicesByStatus->get('partial')?->total_sum ?? 0));

        // Average order value
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / max($completedOrders, 1) : 0;
        $avgOrderValueThisMonth = $ordersThisMonth > 0 ? $revenueThisMonth / $ordersThisMonth : 0;

        // Monthly revenue data for chart (last 6 months) - database agnostic
        $orders = SalesOrder::where('status', 'delivered')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->get(['created_at', 'total']);

        $monthlyRevenue = $orders->groupBy(fn($order) => $order->created_at->format('Y-m'))
            ->map(function ($items, $key) {
                return [
                    'month' => \Carbon\Carbon::createFromFormat('Y-m', $key)->format('M'),
                    'revenue' => $items->sum('total'),
                    'orders' => $items->count(),
                ];
            })
            ->sortKeys()
            ->values();

        // Prepare chart data
        $revenueChartData = [
            'labels' => $monthlyRevenue->pluck('month')->toArray(),
            'revenue' => $monthlyRevenue->pluck('revenue')->toArray(),
            'orders' => $monthlyRevenue->pluck('orders')->toArray(),
        ];

        // Recent orders
        $recentOrders = SalesOrder::with('customer')
            ->latest()
            ->take(5)
            ->get();

        // Top customers
        $topCustomers = Customer::withCount('salesOrders')
            ->withSum('salesOrders', 'total')
            ->orderByDesc('sales_orders_sum_total')
            ->take(5)
            ->get();

        // New customers this month
        $newCustomersThisMonth = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('livewire.sales.index', [
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'totalRevenue' => $totalRevenue,
            'ordersThisMonth' => $ordersThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'ordersLastMonth' => $ordersLastMonth,
            'revenueLastMonth' => $revenueLastMonth,
            'quotations' => $quotations,
            'salesOrders' => $salesOrders,
            'toInvoice' => $toInvoice,
            'toDeliver' => $toDeliver,
            'cancelledOrders' => $cancelledOrders,
            'completedOrders' => $completedOrders,
            'overdueInvoices' => $overdueInvoices,
            'awaitingPayment' => $awaitingPayment,
            'paidInvoices' => $paidInvoices,
            'draftInvoices' => $draftInvoices,
            'sentInvoices' => $sentInvoices,
            'partialInvoices' => $partialInvoices,
            'avgOrderValue' => $avgOrderValue,
            'avgOrderValueThisMonth' => $avgOrderValueThisMonth,
            'monthlyRevenue' => $monthlyRevenue,
            'revenueChartData' => $revenueChartData,
            'recentOrders' => $recentOrders,
            'topCustomers' => $topCustomers,
            'newCustomersThisMonth' => $newCustomersThisMonth,
        ]);
    }
}
