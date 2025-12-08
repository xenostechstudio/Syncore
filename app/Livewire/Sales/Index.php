<?php

namespace App\Livewire\Sales;

use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Overview')]
class Index extends Component
{
    public function render()
    {
        // Stats
        $totalOrders = SalesOrder::count();
        $totalCustomers = Customer::count();
        $totalRevenue = SalesOrder::where('status', 'delivered')->sum('total');
        $pendingOrders = SalesOrder::whereIn('status', ['draft', 'confirmed', 'processing'])->count();
        
        // This month
        $ordersThisMonth = SalesOrder::whereMonth('created_at', now()->month)->count();
        $revenueThisMonth = SalesOrder::where('status', 'delivered')
            ->whereMonth('created_at', now()->month)
            ->sum('total');
        
        // Status breakdown
        $ordersByStatus = SalesOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent orders
        $recentOrders = SalesOrder::with('customer')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.sales.index', [
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'totalRevenue' => $totalRevenue,
            'pendingOrders' => $pendingOrders,
            'ordersThisMonth' => $ordersThisMonth,
            'revenueThisMonth' => $revenueThisMonth,
            'ordersByStatus' => $ordersByStatus,
            'recentOrders' => $recentOrders,
        ]);
    }
}
