<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Inventory Overview')]
class Index extends Component
{
    public string $search = '';
    public string $view = 'list';

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function render()
    {
        // Stats
        $totalItems = InventoryItem::count();
        $totalWarehouses = Warehouse::count();
        $inStockItems = InventoryItem::where('status', 'in_stock')->count();
        $lowStockItems = InventoryItem::where('status', 'low_stock')
            ->orWhere('quantity', '<', 10)
            ->count();
        $outOfStockItems = InventoryItem::where('status', 'out_of_stock')
            ->orWhere('quantity', '=', 0)
            ->count();
        $totalValue = InventoryItem::sum(DB::raw('quantity * cost_price'));
        $totalUnits = InventoryItem::sum('quantity');
        
        // Last 30 days
        $itemsAddedLast30Days = InventoryItem::where('created_at', '>=', now()->subDays(30))->count();
        
        // Recent items
        $recentItems = InventoryItem::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%"))
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.inventory.index', [
            'totalItems' => $totalItems,
            'totalWarehouses' => $totalWarehouses,
            'inStockItems' => $inStockItems,
            'lowStockItems' => $lowStockItems,
            'outOfStockItems' => $outOfStockItems,
            'totalValue' => $totalValue,
            'totalUnits' => $totalUnits,
            'itemsAddedLast30Days' => $itemsAddedLast30Days,
            'recentItems' => $recentItems,
        ]);
    }
}
