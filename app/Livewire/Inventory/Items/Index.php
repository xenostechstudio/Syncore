<?php

namespace App\Livewire\Inventory\Items;

use App\Exports\ProductsExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Products')]
class Index extends Component
{
    use WithIndexComponent;

    #[Url]
    public int $perPage = 15;

    #[Url]
    public ?int $warehouse_id = null;

    public array $visibleColumns = [
        'name' => true,
        'sku' => true,
        'category' => true,
        'stock' => true,
        'price' => true,
        'status' => true,
    ];

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedWarehouseId(): void
    {
        $this->resetPage();
    }

    public function toggleFavorite(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_favorite' => ! $product->is_favorite]);
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);

        $totalStock = DB::table('inventory_stocks')->where('product_id', $id)->sum('quantity');

        if ($totalStock > 0) {
            session()->flash('error', "Cannot delete '{$product->name}'. Product has {$totalStock} units in stock.");
            return;
        }

        $product->delete();
        $this->selected = array_filter($this->selected, fn ($s) => $s != $id);
        session()->flash('success', "Product '{$product->name}' deleted successfully.");
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $products = Product::whereIn('id', $this->selected)->get();

        $stockByProduct = DB::table('inventory_stocks')
            ->whereIn('product_id', $this->selected)
            ->selectRaw('product_id, SUM(quantity) as total_stock')
            ->groupBy('product_id')
            ->pluck('total_stock', 'product_id');

        $canDelete = [];
        $cannotDelete = [];

        foreach ($products as $product) {
            $stock = (int) ($stockByProduct[$product->id] ?? 0);
            if ($stock === 0) {
                $canDelete[] = ['id' => $product->id, 'name' => $product->name, 'sku' => $product->sku];
            } else {
                $cannotDelete[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'stock' => $stock,
                    'reason' => "Has {$stock} units in stock",
                ];
            }
        }

        $this->deleteValidation = [
            'canDelete' => $canDelete,
            'cannotDelete' => $cannotDelete,
            'totalSelected' => count($this->selected),
        ];

        $this->showDeleteConfirm = true;
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $productsWithStock = DB::table('inventory_stocks')
            ->whereIn('product_id', $this->selected)
            ->selectRaw('product_id, SUM(quantity) as total_stock')
            ->groupBy('product_id')
            ->having('total_stock', '>', 0)
            ->pluck('product_id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $productsWithStock));

        if (empty($deletableIds)) {
            session()->flash('error', 'No products can be deleted. All selected products have stock.');
            $this->cancelDelete();
            return;
        }

        $count = Product::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} products deleted successfully.");
    }

    public function bulkActivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Product::whereIn('id', $this->selected)->update(['status' => 'in_stock']);
        $this->clearSelection();
        session()->flash('success', "{$count} products activated.");
    }

    public function bulkDeactivate(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = Product::whereIn('id', $this->selected)->update(['status' => 'out_of_stock']);
        $this->clearSelection();
        session()->flash('success', "{$count} products deactivated.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'products-' . now()->format('Y-m-d') . '.xlsx'
            : 'products-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new ProductsExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return Product::query()
            ->with('category')
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return Product::class;
    }

    public function render()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        if (! $this->warehouse_id) {
            $this->warehouse_id = $warehouses->first()?->id;
        }

        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->oldest(),
            'name' => $this->getQuery()->orderBy('name'),
            'price_high' => $this->getQuery()->orderByDesc('selling_price'),
            'price_low' => $this->getQuery()->orderBy('selling_price'),
            'stock_high' => $this->getQuery()->orderByDesc('quantity'),
            'stock_low' => $this->getQuery()->orderBy('quantity'),
            default => $this->getQuery()->latest(),
        };

        $products = $query->paginate($this->perPage, ['*'], 'page', $this->page);

        if ($this->warehouse_id) {
            $this->injectStockAttributes($products, $this->warehouse_id);
        }

        $productsByStatus = null;
        if ($this->view === 'kanban') {
            $productsByStatus = $this->getQuery()->get()->groupBy('status');
        }

        return view('livewire.inventory.items.index', [
            'items' => $products,
            'itemsByStatus' => $productsByStatus,
            'warehouses' => $warehouses,
        ]);
    }

    private function injectStockAttributes($products, int $warehouseId): void
    {
        $productIds = $products->getCollection()->pluck('id')->values()->all();
        if (empty($productIds)) {
            return;
        }

        $onHand = DB::table('inventory_stocks')
            ->where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->pluck('quantity', 'product_id');

        $forecastIn = DB::table('inventory_adjustment_items as iai')
            ->join('inventory_adjustments as ia', 'ia.id', '=', 'iai.inventory_adjustment_id')
            ->where('ia.warehouse_id', $warehouseId)
            ->whereNull('ia.posted_at')
            ->where('ia.adjustment_type', 'increase')
            ->whereNotIn('ia.status', ['cancelled'])
            ->whereIn('iai.product_id', $productIds)
            ->selectRaw('iai.product_id, SUM(iai.counted_quantity) as qty')
            ->groupBy('iai.product_id')
            ->pluck('qty', 'iai.product_id');

        $forecastOut = DB::table('delivery_order_items as doi')
            ->join('delivery_orders as do', 'do.id', '=', 'doi.delivery_order_id')
            ->join('sales_order_items as soi', 'soi.id', '=', 'doi.sales_order_item_id')
            ->where('do.warehouse_id', $warehouseId)
            ->whereNotIn('do.status', ['delivered', 'returned'])
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('inventory_adjustments as ia2')
                    ->whereColumn('ia2.source_delivery_order_id', 'do.id')
                    ->whereNotNull('ia2.posted_at');
            })
            ->whereIn('soi.product_id', $productIds)
            ->selectRaw('soi.product_id, SUM(doi.quantity) as qty')
            ->groupBy('soi.product_id')
            ->pluck('qty', 'soi.product_id');

        $products->getCollection()->transform(function ($item) use ($onHand, $forecastIn, $forecastOut) {
            $pid = $item->id;
            $hand = (int) ($onHand[$pid] ?? 0);
            $in = (int) ($forecastIn[$pid] ?? 0);
            $out = (int) ($forecastOut[$pid] ?? 0);

            $item->setAttribute('on_hand', $hand);
            $item->setAttribute('forecast_in', $in);
            $item->setAttribute('forecast_out', $out);
            $item->setAttribute('available', $hand + $in - $out);

            return $item;
        });
    }
}
