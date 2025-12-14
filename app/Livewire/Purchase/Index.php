<?php

namespace App\Livewire\Purchase;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Purchase'])]
#[Title('Purchase Overview')]
class Index extends Component
{
    public function render()
    {
        // Stats
        $totalOrders = DB::table('purchase_rfqs')->where('status', 'purchase_order')->count();
        $totalSuppliers = DB::table('suppliers')->where('is_active', true)->count();
        $totalSpent = DB::table('purchase_rfqs')->where('status', 'done')->sum('total');
        $pendingOrders = DB::table('purchase_rfqs')->where('status', 'purchase_order')->count();
        
        // This month
        $ordersThisMonth = DB::table('purchase_rfqs')
            ->where('status', 'purchase_order')
            ->whereMonth('created_at', now()->month)
            ->count();
        $spentThisMonth = DB::table('purchase_rfqs')
            ->where('status', 'done')
            ->whereMonth('created_at', now()->month)
            ->sum('total');
        
        // Status breakdown
        $ordersByStatus = DB::table('purchase_rfqs')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent orders
        $recentOrders = DB::table('purchase_rfqs')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.purchase.index', [
            'totalOrders' => $totalOrders,
            'totalSuppliers' => $totalSuppliers,
            'totalSpent' => $totalSpent,
            'pendingOrders' => $pendingOrders,
            'ordersThisMonth' => $ordersThisMonth,
            'spentThisMonth' => $spentThisMonth,
            'ordersByStatus' => $ordersByStatus,
            'recentOrders' => $recentOrders,
        ]);
    }
}
