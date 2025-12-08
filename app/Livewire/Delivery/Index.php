<?php

namespace App\Livewire\Delivery;

use App\Models\Delivery\DeliveryOrder;
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
        // Stats
        $totalDeliveries = DeliveryOrder::count();
        $pendingDeliveries = DeliveryOrder::whereIn('status', ['pending', 'picked'])->count();
        $inTransit = DeliveryOrder::where('status', 'in_transit')->count();
        $delivered = DeliveryOrder::where('status', 'delivered')->count();
        
        // This month
        $deliveriesThisMonth = DeliveryOrder::whereMonth('created_at', now()->month)->count();
        $deliveredThisMonth = DeliveryOrder::where('status', 'delivered')
            ->whereMonth('actual_delivery_date', now()->month)
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

        return view('livewire.delivery.index', [
            'totalDeliveries' => $totalDeliveries,
            'pendingDeliveries' => $pendingDeliveries,
            'inTransit' => $inTransit,
            'delivered' => $delivered,
            'deliveriesThisMonth' => $deliveriesThisMonth,
            'deliveredThisMonth' => $deliveredThisMonth,
            'deliveriesByStatus' => $deliveriesByStatus,
            'recentDeliveries' => $recentDeliveries,
        ]);
    }
}
