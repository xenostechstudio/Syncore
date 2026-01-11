<?php

namespace App\Livewire\Delivery;

use App\Models\Delivery\DeliveryOrder;
use App\Models\Sales\SalesOrder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Delivery'])]
#[Title('Delivery Overview')]
class Index extends Component
{
    public function render()
    {
        // Main Stats
        $totalDeliveries = DeliveryOrder::count();
        $pendingDeliveries = DeliveryOrder::whereIn('status', ['pending', 'picked'])->count();
        $inTransit = DeliveryOrder::where('status', 'in_transit')->count();
        $delivered = DeliveryOrder::where('status', 'delivered')->count();
        
        // This month stats
        $deliveriesThisMonth = DeliveryOrder::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $deliveredThisMonth = DeliveryOrder::where('status', 'delivered')
            ->whereMonth('actual_delivery_date', now()->month)
            ->whereYear('actual_delivery_date', now()->year)
            ->count();
        
        // Last month stats for comparison
        $deliveriesLastMonth = DeliveryOrder::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $deliveredLastMonth = DeliveryOrder::where('status', 'delivered')
            ->whereMonth('actual_delivery_date', now()->subMonth()->month)
            ->whereYear('actual_delivery_date', now()->subMonth()->year)
            ->count();
        
        // Status breakdown
        $deliveriesByStatus = DeliveryOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Recent deliveries
        $recentDeliveries = DeliveryOrder::with(['salesOrder.customer', 'warehouse'])
            ->latest()
            ->take(5)
            ->get();

        // Pending Sales Orders (ready for delivery)
        $pendingSalesOrders = SalesOrder::where('status', 'sales_order')
            ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_delivered'))
            ->whereDoesntHave('deliveryOrders', fn($q) => $q->whereNotIn('status', ['cancelled', 'delivered']))
            ->count();

        // Monthly delivery data for chart (last 6 months)
        $monthlyDeliveries = DeliveryOrder::where('status', 'delivered')
            ->where('actual_delivery_date', '>=', now()->subMonths(6)->startOfMonth())
            ->select(
                DB::raw("EXTRACT(YEAR FROM actual_delivery_date) as year"),
                DB::raw("EXTRACT(MONTH FROM actual_delivery_date) as month"),
                DB::raw('COUNT(*) as deliveries')
            )
            ->groupBy(DB::raw("EXTRACT(YEAR FROM actual_delivery_date)"), DB::raw("EXTRACT(MONTH FROM actual_delivery_date)"))
            ->orderBy(DB::raw("EXTRACT(YEAR FROM actual_delivery_date)"))
            ->orderBy(DB::raw("EXTRACT(MONTH FROM actual_delivery_date)"))
            ->get()
            ->map(function ($item) {
                return [
                    'month' => date('M', mktime(0, 0, 0, (int) $item->month, 1)),
                    'deliveries' => $item->deliveries,
                ];
            });

        // Top couriers
        $topCouriers = DeliveryOrder::select('courier', DB::raw('COUNT(*) as count'))
            ->whereNotNull('courier')
            ->where('courier', '!=', '')
            ->groupBy('courier')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Average delivery time (days from creation to delivery)
        $avgDeliveryTime = DeliveryOrder::where('status', 'delivered')
            ->whereNotNull('actual_delivery_date')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (actual_delivery_date - created_at)) / 86400) as avg_days')
            ->value('avg_days') ?? 0;

        // Failed/Returned deliveries
        $failedDeliveries = DeliveryOrder::where('status', 'failed')->count();
        $returnedDeliveries = DeliveryOrder::where('status', 'returned')->count();

        return view('livewire.delivery.index', [
            'totalDeliveries' => $totalDeliveries,
            'pendingDeliveries' => $pendingDeliveries,
            'inTransit' => $inTransit,
            'delivered' => $delivered,
            'deliveriesThisMonth' => $deliveriesThisMonth,
            'deliveredThisMonth' => $deliveredThisMonth,
            'deliveriesLastMonth' => $deliveriesLastMonth,
            'deliveredLastMonth' => $deliveredLastMonth,
            'deliveriesByStatus' => $deliveriesByStatus,
            'recentDeliveries' => $recentDeliveries,
            'pendingSalesOrders' => $pendingSalesOrders,
            'monthlyDeliveries' => $monthlyDeliveries,
            'topCouriers' => $topCouriers,
            'avgDeliveryTime' => round($avgDeliveryTime, 1),
            'failedDeliveries' => $failedDeliveries,
            'returnedDeliveries' => $returnedDeliveries,
        ]);
    }
}
