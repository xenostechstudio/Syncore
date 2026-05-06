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
        // Single scan over products: count + status buckets + value/unit
        // sums. Was 6 separate queries; the low/out-of-stock buckets keep
        // their original OR-on-quantity semantics via CASE WHEN.
        $stats = Product::query()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'in_stock' THEN 1 ELSE 0 END) as in_stock,
                SUM(CASE WHEN status = 'low_stock' OR quantity < 10 THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN status = 'out_of_stock' OR quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(quantity * cost_price) as total_value,
                SUM(quantity) as total_units
            ")
            ->first();
        $totalProducts = (int) ($stats->total ?? 0);
        $inStockProducts = (int) ($stats->in_stock ?? 0);
        $lowStockProducts = (int) ($stats->low_stock ?? 0);
        $outOfStockProducts = (int) ($stats->out_of_stock ?? 0);
        $totalValue = (float) ($stats->total_value ?? 0);
        $totalUnits = (int) ($stats->total_units ?? 0);

        $totalWarehouses = Warehouse::count();
        
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
