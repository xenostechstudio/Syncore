<?php

namespace App\Livewire\Sales\Reports;

use App\Models\Inventory\Product;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Reports')]
class Index extends Component
{
    #[Url]
    public string $period = 'this_month';
    
    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->setPeriodDates();
    }

    public function updatedPeriod(): void
    {
        $this->setPeriodDates();
    }

    protected function setPeriodDates(): void
    {
        [$start, $end] = match ($this->period) {
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');
    }

    protected function getSummary(Carbon $startDate, Carbon $endDate): array
    {
        $orders = SalesOrder::whereBetween('created_at', [$startDate, $endDate]);
        $completedOrders = SalesOrder::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalSales = (clone $completedOrders)->sum('total');
        $totalOrders = (clone $orders)->count();
        $completedCount = (clone $completedOrders)->count();
        $avgOrderValue = $completedCount > 0 ? $totalSales / $completedCount : 0;

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'completed_orders' => $completedCount,
            'avg_order_value' => $avgOrderValue,
        ];
    }

    protected function getChartData(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'salesTrend' => $this->getSalesTrendData($startDate, $endDate),
            'orderStatus' => $this->getOrderStatusData($startDate, $endDate),
            'topProducts' => $this->getTopProductsData($startDate, $endDate),
        ];
    }

    protected function getSalesTrendData(Carbon $startDate, Carbon $endDate): array
    {
        $orders = SalesOrder::where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get(['created_at', 'total']);

        $grouped = $orders->groupBy(fn($order) => $order->created_at->format('Y-m-d'));

        $labels = [];
        $values = [];
        
        foreach ($grouped as $date => $items) {
            $labels[] = Carbon::parse($date)->format('M d');
            $values[] = $items->sum('total');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    protected function getOrderStatusData(Carbon $startDate, Carbon $endDate): array
    {
        $statuses = SalesOrder::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [
            'draft' => 'Quotation',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $labels = [];
        $values = [];
        
        foreach ($statuses as $status => $count) {
            $labels[] = $statusLabels[$status] ?? ucfirst($status);
            $values[] = $count;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    protected function getTopProductsData(Carbon $startDate, Carbon $endDate): array
    {
        $products = Product::select('products.name', DB::raw('SUM(sales_order_items.total) as total_sales'))
            ->join('sales_order_items', 'products.id', '=', 'sales_order_items.product_id')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_orders.status', 'delivered')
            ->whereBetween('sales_orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        $labels = $products->pluck('name')->map(fn($n) => strlen($n) > 20 ? substr($n, 0, 20) . '...' : $n)->toArray();
        $values = $products->pluck('total_sales')->toArray();

        return ['labels' => $labels, 'values' => $values];
    }

    public function render()
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        return view('livewire.sales.reports.index', [
            'summary' => $this->getSummary($startDate, $endDate),
            'chartData' => $this->getChartData($startDate, $endDate),
        ]);
    }
}
