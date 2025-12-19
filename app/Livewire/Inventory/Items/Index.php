<?php

namespace App\Livewire\Inventory\Items;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Inventory'])]
#[Title('Products')]
class Index extends Component
{
    use WithManualPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $status = '';
    
    #[Url]
    public string $sort = 'latest';
    
    #[Url]
    public int $perPage = 15;
    
    #[Url]
    public string $view = 'list';

    #[Url]
    public ?int $warehouse_id = null;

    public array $selected = [];
    public bool $selectAll = false;

    public array $visibleColumns = [
        'name' => true,
        'sku' => true,
        'category' => true,
        'stock' => true,
        'price' => true,
        'status' => true,
    ];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = Product::query()
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%"))
                ->when($this->status, fn($q) => $q->where('status', $this->status))
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort']);
        $this->resetPage();
    }

    public function toggleFavorite(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->update(['is_favorite' => !$product->is_favorite]);
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        $this->selected = array_filter($this->selected, fn($s) => $s != $id);
    }

    public function deleteSelected(): void
    {
        Product::whereIn('id', $this->selected)->delete();
        $this->clearSelection();
    }

    public function render()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        if (! $this->warehouse_id) {
            $this->warehouse_id = $warehouses->first()?->id;
        }

        $products = Product::query()
            ->with('category')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest())
            ->when($this->sort === 'name', fn($q) => $q->orderBy('name'))
            ->when($this->sort === 'price_high', fn($q) => $q->orderByDesc('selling_price'))
            ->when($this->sort === 'price_low', fn($q) => $q->orderBy('selling_price'))
            ->when($this->sort === 'stock_high', fn($q) => $q->orderByDesc('quantity'))
            ->when($this->sort === 'stock_low', fn($q) => $q->orderBy('quantity'))
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        if ($this->warehouse_id) {
            $productIds = $products->getCollection()->pluck('id')->values()->all();

            $onHand = DB::table('inventory_stocks')
                ->where('warehouse_id', $this->warehouse_id)
                ->whereIn('product_id', $productIds)
                ->pluck('quantity', 'product_id');

            $forecastIn = DB::table('inventory_adjustment_items as iai')
                ->join('inventory_adjustments as ia', 'ia.id', '=', 'iai.inventory_adjustment_id')
                ->where('ia.warehouse_id', $this->warehouse_id)
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
                ->where('do.warehouse_id', $this->warehouse_id)
                ->whereNotIn('do.status', ['delivered', 'returned'])
                ->whereNotExists(function ($q) {
                    $q->selectRaw('1')
                        ->from('inventory_adjustments as ia2')
                        ->whereColumn('ia2.source_delivery_order_id', 'do.id')
                        ->whereNotNull('ia2.posted_at');
                })
                ->whereIn('soi.product_id', $productIds)
                ->selectRaw('soi.product_id, SUM(doi.quantity_to_deliver) as qty')
                ->groupBy('soi.product_id')
                ->pluck('qty', 'soi.product_id');

            $products->getCollection()->transform(function ($item) use ($onHand, $forecastIn, $forecastOut) {
                $pid = $item->id;
                $hand = (int) ($onHand[$pid] ?? 0);
                $in = (int) ($forecastIn[$pid] ?? 0);
                $out = (int) ($forecastOut[$pid] ?? 0);
                $available = $hand + $in - $out;

                $item->setAttribute('on_hand', $hand);
                $item->setAttribute('forecast_in', $in);
                $item->setAttribute('forecast_out', $out);
                $item->setAttribute('available', $available);
                return $item;
            });
        }

        // Group products by status for kanban view
        $productsByStatus = null;
        if ($this->view === 'kanban') {
            $productsByStatus = Product::query()
                ->with('category')
                ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%"))
                ->get()
                ->groupBy('status');
        }

        return view('livewire.inventory.items.index', [
            'items' => $products,
            'itemsByStatus' => $productsByStatus,
            'warehouses' => $warehouses,
        ]);
    }
}
