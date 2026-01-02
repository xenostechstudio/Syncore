<?php

namespace App\Services\Reports;

use App\Models\Sales\SalesOrder;
use App\Models\Sales\Customer;
use App\Models\Inventory\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesReportService
{
    public function getSalesByPeriod(Carbon $startDate, Carbon $endDate, string $groupBy = 'day'): array
    {
        $dateFormat = match ($groupBy) {
            'day' => 'Y-m-d',
            'week' => 'Y-W',
            'month' => 'Y-m',
            'year' => 'Y',
            default => 'Y-m-d',
        };

        $dbFormat = match ($groupBy) {
            'day' => "TO_CHAR(order_date, 'YYYY-MM-DD')",
            'week' => "TO_CHAR(order_date, 'IYYY-IW')",
            'month' => "TO_CHAR(order_date, 'YYYY-MM')",
            'year' => "TO_CHAR(order_date, 'YYYY')",
            default => "TO_CHAR(order_date, 'YYYY-MM-DD')",
        };

        return SalesOrder::query()
            ->selectRaw("{$dbFormat} as period, COUNT(*) as order_count, SUM(total) as total_sales")
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'completed'])
            ->groupByRaw($dbFormat)
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    public function getSalesByCustomer(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        return SalesOrder::query()
            ->select('customer_id')
            ->selectRaw('COUNT(*) as order_count, SUM(total) as total_sales')
            ->with('customer:id,name,email')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'completed'])
            ->groupBy('customer_id')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getSalesByProduct(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        return DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', 'products.sku')
            ->selectRaw('SUM(sales_order_items.quantity) as total_quantity, SUM(sales_order_items.total) as total_sales')
            ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
            ->whereIn('sales_orders.status', ['confirmed', 'processing', 'completed'])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getSalespersonPerformance(Carbon $startDate, Carbon $endDate): array
    {
        return SalesOrder::query()
            ->select('user_id')
            ->selectRaw('COUNT(*) as order_count, SUM(total) as total_sales, AVG(total) as avg_order_value')
            ->with('user:id,name,email')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'completed'])
            ->groupBy('user_id')
            ->orderByDesc('total_sales')
            ->get()
            ->toArray();
    }

    public function getSummary(Carbon $startDate, Carbon $endDate): array
    {
        $orders = SalesOrder::query()
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'completed']);

        $totalOrders = (clone $orders)->count();
        $totalSales = (clone $orders)->sum('total');
        $avgOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endDate])->count();

        return [
            'total_orders' => $totalOrders,
            'total_sales' => $totalSales,
            'avg_order_value' => $avgOrderValue,
            'new_customers' => $newCustomers,
        ];
    }
}
