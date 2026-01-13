<?php

namespace App\Livewire\Inventory;

use App\Models\Inventory\Product;
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
        // Stats (always show total, not filtered)
        $totalProducts = Product::count();
        $totalWarehouses = Warehouse::count();
        $inStockProducts = Product::where('status', 'in_stock')->count();
        $lowStockProducts = Product::where('status', 'low_stock')
            ->orWhere('quantity', '<', 10)
            ->count();
        $outOfStockProducts = Product::where('status', 'out_of_stock')
            ->orWhere('quantity', '=', 0)
            ->count();
        $totalValue = Product::sum(DB::raw('quantity * cost_price'));
        $totalUnits = Product::sum('quantity');
        
        // Last 30 days
        $productsAddedLast30Days = Product::where('created_at', '>=', now()->subDays(30))->count();
        
        // Recent items for left sidebar (always unfiltered, last 5)
        $recentItems = Product::query()
            ->latest()
            ->take(5)
            ->get();

        // Items for main table (filtered by search)
        $items = Product::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%"))
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.inventory.index', [
            'totalItems' => $totalProducts,
            'totalWarehouses' => $totalWarehouses,
            'inStockItems' => $inStockProducts,
            'lowStockItems' => $lowStockProducts,
            'outOfStockItems' => $outOfStockProducts,
            'totalValue' => $totalValue,
            'totalUnits' => $totalUnits,
            'itemsAddedLast30Days' => $productsAddedLast30Days,
            'recentItems' => $recentItems,
            'items' => $items,
        ]);
    }
}
