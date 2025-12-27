<?php

namespace App\Livewire\Sales;

use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

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
        
        // Quotations & Sales Orders
        $quotations = SalesOrder::whereIn('status', ['draft', 'confirmed'])->count();
        $salesOrders = SalesOrder::where('status', 'processing')->count();
        
        // To Invoice & To Deliver
        $toInvoice = SalesOrder::where('status', 'processing')
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_invoiced'))
            ->count();
        $toDeliver = SalesOrder::where('status', 'processing')
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_delivered'))
            ->count();

        // Invoice stats
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        $awaitingPayment = Invoice::whereIn('status', ['sent', 'partial'])->sum('total');

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

        return view('livewire.sales.index', [
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'totalRevenue' => $totalRevenue,
            'ordersThisMonth' => $ordersThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'quotations' => $quotations,
            'salesOrders' => $salesOrders,
            'toInvoice' => $toInvoice,
            'toDeliver' => $toDeliver,
            'overdueInvoices' => $overdueInvoices,
            'awaitingPayment' => $awaitingPayment,
            'recentOrders' => $recentOrders,
            'topCustomers' => $topCustomers,
        ]);
    }
}
