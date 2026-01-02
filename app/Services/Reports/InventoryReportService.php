<?php

namespace App\Services\Reports;

use App\Models\Inventory\Product;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;

class InventoryReportService
{
    public function getStockValuation(?int $warehouseId = null): array
    {
        $query = Product::query()
            ->select('products.*')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity), 0) as total_stock')
            ->leftJoin('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
            ->groupBy('products.id');

        if ($warehouseId) {
            $query->where('inventory_stocks.warehouse_id', $warehouseId);
        }

        $products = $query->get();

        $totalValue = 0;
        $items = [];

        foreach ($products as $product) {
            $stockValue = $product->total_stock * $product->cost_price;
            $totalValue += $stockValue;

            $items[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'quantity' => $product->total_stock,
                'cost_price' => $product->cost_price,
                'stock_value' => $stockValue,
            ];
        }

        return [
            'items' => $items,
            'total_value' => $totalValue,
            'total_products' => count($items),
        ];
    }

    public function getLowStockProducts(int $threshold = 10): array
    {
        return Product::query()
            ->select('products.*')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity), 0) as total_stock')
            ->leftJoin('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
            ->groupBy('products.id')
            ->havingRaw('COALESCE(SUM(inventory_stocks.quantity), 0) <= ?', [$threshold])
            ->orderByRaw('COALESCE(SUM(inventory_stocks.quantity), 0) ASC')
            ->get()
            ->toArray();
    }

    public function getStockByWarehouse(): array
    {
        return Warehouse::query()
            ->select('warehouses.*')
            ->selectRaw('COUNT(DISTINCT inventory_stocks.product_id) as product_count')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity), 0) as total_stock')
            ->leftJoin('inventory_stocks', 'warehouses.id', '=', 'inventory_stocks.warehouse_id')
            ->groupBy('warehouses.id')
            ->get()
            ->toArray();
    }

    public function getOutOfStockProducts(): array
    {
        return Product::query()
            ->select('products.*')
            ->selectRaw('COALESCE(SUM(inventory_stocks.quantity), 0) as total_stock')
            ->leftJoin('inventory_stocks', 'products.id', '=', 'inventory_stocks.product_id')
            ->groupBy('products.id')
            ->havingRaw('COALESCE(SUM(inventory_stocks.quantity), 0) = 0')
            ->get()
            ->toArray();
    }

    public function getSummary(): array
    {
        $totalProducts = Product::count();
        $totalStock = InventoryStock::sum('quantity');
        
        $valuation = $this->getStockValuation();
        $lowStock = count($this->getLowStockProducts());
        $outOfStock = count($this->getOutOfStockProducts());

        return [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
            'total_value' => $valuation['total_value'],
            'low_stock_count' => $lowStock,
            'out_of_stock_count' => $outOfStock,
        ];
    }
}
