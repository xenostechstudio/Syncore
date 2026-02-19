<?php

namespace App\Services;

use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Purchase\VendorBill;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Services\Reports\ReportService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard Service
 * 
 * Provides aggregated metrics and data for the application dashboard.
 * Includes sales, invoice, inventory, and purchase metrics with caching support.
 * 
 * @package App\Services
 */
class DashboardService
{
    /** @var int Cache duration in seconds (5 minutes) */
    protected const CACHE_TTL = 300;

    /**
     * Get sales metrics for a date range with month-over-month comparison.
     *
     * @param Carbon|null $startDate Start of the date range (defaults to start of current month)
     * @param Carbon|null $endDate End of the date range (defaults to end of current month)
     * @return array{total_sales: float, total_orders: int, average_order_value: float, sales_change: float, orders_change: float}
     */
    public static function getSalesMetrics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();
        
        $cacheKey = 'dashboard_sales_metrics_' . $startDate->format('Y-m-d') . '_' . $endDate->format('Y-m-d');

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($startDate, $endDate) {
            $previousStart = $startDate->copy()->subMonth();
            $previousEnd = $endDate->copy()->subMonth();

            $currentSales = SalesOrder::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotIn('status', ['cancelled'])
                ->sum('total');

            $previousSales = SalesOrder::whereBetween('created_at', [$previousStart, $previousEnd])
                ->whereNotIn('status', ['cancelled'])
                ->sum('total');

            $currentOrders = SalesOrder::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotIn('status', ['cancelled'])
                ->count();

            $previousOrders = SalesOrder::whereBetween('created_at', [$previousStart, $previousEnd])
                ->whereNotIn('status', ['cancelled'])
                ->count();

            return [
                'total_sales' => $currentSales,
                'total_orders' => $currentOrders,
                'average_order_value' => $currentOrders > 0 ? $currentSales / $currentOrders : 0,
                'sales_change' => $previousSales > 0 ? (($currentSales - $previousSales) / $previousSales) * 100 : 0,
                'orders_change' => $previousOrders > 0 ? (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0,
            ];
        });
    }

    /**
     * Get invoice-related metrics including outstanding and overdue amounts.
     *
     * @return array{total_outstanding: float, overdue_amount: float, overdue_count: int, paid_this_month: float}
     */
    public static function getInvoiceMetrics(): array
    {
        return Cache::remember('dashboard_invoice_metrics', self::CACHE_TTL, function () {
            $totalOutstanding = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                ->selectRaw('COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as outstanding')
                ->value('outstanding') ?? 0;

            $overdueAmount = Invoice::where('status', 'overdue')
                ->selectRaw('COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as overdue')
                ->value('overdue') ?? 0;

            $overdueCount = Invoice::where('status', 'overdue')->count();

            $paidThisMonth = Invoice::where('status', 'paid')
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('total');

            return [
                'total_outstanding' => $totalOutstanding,
                'overdue_amount' => $overdueAmount,
                'overdue_count' => $overdueCount,
                'paid_this_month' => $paidThisMonth,
            ];
        });
    }

    /**
     * Get inventory metrics including stock levels and total value.
     *
     * @return array{total_products: int, low_stock_count: int, out_of_stock_count: int, total_inventory_value: float}
     */
    public static function getInventoryMetrics(): array
    {
        return Cache::remember('dashboard_inventory_metrics', self::CACHE_TTL, function () {
            $totalProducts = Product::where('status', 'active')->count();
            
            $lowStockThreshold = config('inventory.low_stock_threshold', 10);
            $lowStockCount = Product::where('status', 'active')
                ->where('quantity', '<=', $lowStockThreshold)
                ->where('quantity', '>', 0)
                ->count();
                
            $outOfStockCount = Product::where('status', 'active')
                ->where('quantity', '<=', 0)
                ->count();
                
            $totalValue = Product::where('status', 'active')
                ->selectRaw('COALESCE(SUM(quantity * cost_price), 0) as value')
                ->value('value') ?? 0;

            return [
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'total_inventory_value' => $totalValue,
            ];
        });
    }

    /**
     * Get purchase/vendor bill metrics.
     *
     * @return array{pending_bills: float, overdue_bills: float, paid_this_month: float}
     */
    public static function getPurchaseMetrics(): array
    {
        return Cache::remember('dashboard_purchase_metrics', self::CACHE_TTL, function () {
            $pendingBills = VendorBill::whereIn('status', ['pending', 'partial'])
                ->selectRaw('COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as pending')
                ->value('pending') ?? 0;

            $overdueBills = VendorBill::where('status', 'overdue')
                ->selectRaw('COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as overdue')
                ->value('overdue') ?? 0;

            $paidThisMonth = VendorBill::where('status', 'paid')
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('total');

            return [
                'pending_bills' => $pendingBills,
                'overdue_bills' => $overdueBills,
                'paid_this_month' => $paidThisMonth,
            ];
        });
    }

    /**
     * Get top customers by total sales value.
     *
     * @param int $limit Maximum number of customers to return
     * @return array<int, array{id: int, name: string, total_sales: float, order_count: int}>
     */
    public static function getTopCustomers(int $limit = 5): array
    {
        return Cache::remember('dashboard_top_customers_' . $limit, self::CACHE_TTL, function () use ($limit) {
            return Customer::select('customers.*')
                ->selectRaw('SUM(sales_orders.total) as total_sales')
                ->selectRaw('COUNT(sales_orders.id) as order_count')
                ->leftJoin('sales_orders', 'customers.id', '=', 'sales_orders.customer_id')
                ->whereNotNull('sales_orders.id')
                ->whereNotIn('sales_orders.status', ['cancelled'])
                ->groupBy('customers.id')
                ->orderByDesc('total_sales')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get top products by revenue.
     *
     * @param int $limit Maximum number of products to return
     * @return array<int, array{id: int, name: string, total_sold: int, total_revenue: float}>
     */
    public static function getTopProducts(int $limit = 5): array
    {
        return Cache::remember('dashboard_top_products_' . $limit, self::CACHE_TTL, function () use ($limit) {
            return Product::select('products.*')
                ->selectRaw('SUM(sales_order_items.quantity) as total_sold')
                ->selectRaw('SUM(sales_order_items.total) as total_revenue')
                ->leftJoin('sales_order_items', 'products.id', '=', 'sales_order_items.product_id')
                ->leftJoin('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
                ->whereNotNull('sales_order_items.id')
                ->whereNotIn('sales_orders.status', ['cancelled'])
                ->groupBy('products.id')
                ->orderByDesc('total_revenue')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get monthly sales data for chart visualization.
     *
     * @param int $months Number of months to include
     * @return array<int, array{month: string, sales: float}>
     */
    public static function getSalesChartData(int $months = 6): array
    {
        return Cache::remember('dashboard_sales_chart_' . $months, self::CACHE_TTL, function () use ($months) {
            $data = [];
            
            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $sales = SalesOrder::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->whereNotIn('status', ['cancelled'])
                    ->sum('total');

                $data[] = [
                    'month' => $date->format('M Y'),
                    'sales' => $sales,
                ];
            }

            return $data;
        });
    }

    /**
     * Get products with low stock levels.
     *
     * @param int $limit Maximum number of products to return
     * @return array<int, array>
     */
    public static function getLowStockProducts(int $limit = 10): array
    {
        return Cache::remember('dashboard_low_stock_' . $limit, self::CACHE_TTL, function () use ($limit) {
            $lowStockThreshold = config('inventory.low_stock_threshold', 10);
            
            return Product::where('status', 'active')
                ->where('quantity', '<=', $lowStockThreshold)
                ->orderBy('quantity')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    /**
     * Get recent activity logs from the custom activity_logs table.
     *
     * @param int $limit Maximum number of activities to return
     * @return array<int, array{id: int, description: string, subject_type: string, causer_name: string, created_at: string}>
     */
    public static function getRecentActivities(int $limit = 10): array
    {
        // Cache activities for shorter duration as they are real-time
        return Cache::remember('dashboard_recent_activities_' . $limit, 60, function () use ($limit) {
            return DB::table('activity_logs')
                ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
                ->select('activity_logs.*', 'users.name as causer_name')
                ->orderByDesc('activity_logs.created_at')
                ->limit($limit)
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'description' => $activity->description,
                        'subject_type' => class_basename($activity->model_type ?? ''),
                        'causer_name' => $activity->causer_name ?? $activity->user_name ?? 'System',
                        'created_at' => \Carbon\Carbon::parse($activity->created_at),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get summary of pending actions requiring attention.
     *
     * @return array{pending_quotations: int, orders_to_invoice: int, orders_to_deliver: int, draft_invoices: int, overdue_invoices: int, pending_bills: int}
     */
    public static function getPendingActions(): array
    {
        return Cache::remember('dashboard_pending_actions', self::CACHE_TTL, function () {
            $pendingQuotations = SalesOrder::whereIn('status', ['draft', 'confirmed'])->count();
            $ordersToInvoice = SalesOrder::where('status', 'sales_order')
                ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_invoiced'))
                ->count();
            $ordersToDeliver = SalesOrder::where('status', 'sales_order')
                ->whereHas('items', fn($q) => $q->whereRaw('quantity > quantity_delivered'))
                ->count();
            $draftInvoices = Invoice::where('status', 'draft')->count();
            $overdueInvoices = Invoice::where('status', 'overdue')->count();
            $pendingBills = VendorBill::whereIn('status', ['draft', 'pending'])->count();

            return [
                'pending_quotations' => $pendingQuotations,
                'orders_to_invoice' => $ordersToInvoice,
                'orders_to_deliver' => $ordersToDeliver,
                'draft_invoices' => $draftInvoices,
                'overdue_invoices' => $overdueInvoices,
                'pending_bills' => $pendingBills,
            ];
        });
    }

    /**
     * Get cash flow summary including receivables and payables.
     *
     * @return array{receivables: float, payables: float, received_this_month: float, paid_this_month: float, net_cash_flow: float}
     */
    public static function getCashFlowSummary(): array
    {
        return Cache::remember('dashboard_cash_flow', self::CACHE_TTL, function () {
            $receivables = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
                ->selectRaw('COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as total')
                ->value('total') ?? 0;

            $payables = VendorBill::whereIn('status', ['pending', 'partial', 'overdue'])
                ->selectRaw('COALESCE(SUM(total - COALESCE(paid_amount, 0)), 0) as total')
                ->value('total') ?? 0;

            $receivedThisMonth = Invoice::where('status', 'paid')
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('total');

            $paidThisMonth = VendorBill::where('status', 'paid')
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('total');

            return [
                'receivables' => $receivables,
                'payables' => $payables,
                'received_this_month' => $receivedThisMonth,
                'paid_this_month' => $paidThisMonth,
                'net_cash_flow' => $receivedThisMonth - $paidThisMonth,
            ];
        });
    }

    /**
     * Get recent sales orders.
     *
     * @param int $limit Maximum number of orders to return
     * @return array<int, array{id: int, order_number: string, customer_name: string, total: float, status: string, created_at: string}>
     */
    public static function getRecentOrders(int $limit = 5): array
    {
        return Cache::remember('dashboard_recent_orders_' . $limit, self::CACHE_TTL, function () use ($limit) {
            return SalesOrder::with('customer')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer?->name ?? 'N/A',
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                ])
                ->toArray();
        });
    }

    /**
     * Get recent invoices.
     *
     * @param int $limit Maximum number of invoices to return
     * @return array<int, array{id: int, invoice_number: string, customer_name: string, total: float, status: string, due_date: string}>
     */
    public static function getRecentInvoices(int $limit = 5): array
    {
        return Cache::remember('dashboard_recent_invoices_' . $limit, self::CACHE_TTL, function () use ($limit) {
            return Invoice::with('customer')
                ->latest()
                ->limit($limit)
                ->get()
                ->map(fn ($invoice) => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer?->name ?? 'N/A',
                    'total' => $invoice->total,
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date,
                ])
                ->toArray();
        });
    }

    /**
     * Get all dashboard data with optional caching.
     *
     * @param bool $useCache Whether to use cached data
     * @return array All dashboard metrics and data
     */
    public static function getAllDashboardData(bool $useCache = true): array
    {
        $cacheKey = 'dashboard_data_' . now()->format('Y-m-d-H');

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $data = [
            'sales' => self::getSalesMetrics(),
            'invoices' => self::getInvoiceMetrics(),
            'inventory' => self::getInventoryMetrics(),
            'purchases' => self::getPurchaseMetrics(),
            'pending_actions' => self::getPendingActions(),
            'cash_flow' => self::getCashFlowSummary(),
            'top_customers' => self::getTopCustomers(),
            'top_products' => self::getTopProducts(),
            'sales_chart' => self::getSalesChartData(),
            'low_stock' => self::getLowStockProducts(),
            'recent_activities' => self::getRecentActivities(),
            'recent_orders' => self::getRecentOrders(),
            'recent_invoices' => self::getRecentInvoices(),
        ];

        if ($useCache) {
            Cache::put($cacheKey, $data, self::CACHE_TTL);
        }

        return $data;
    }

    /**
     * Get module-specific widget data using ReportService.
     *
     * @param string $module Module name (sales, inventory, invoicing, hr, crm, purchase)
     * @param bool $useCache Whether to use cached data
     * @return array Widget data for the specified module
     */
    public static function getModuleWidgetData(string $module, bool $useCache = true): array
    {
        $reportService = new ReportService();

        return match ($module) {
            'sales' => $reportService->getSalesWidgetData($useCache),
            'inventory' => $reportService->getInventoryWidgetData($useCache),
            'invoicing' => $reportService->getInvoicingWidgetData($useCache),
            'hr' => $reportService->getHRWidgetData($useCache),
            'crm' => $reportService->getCRMWidgetData($useCache),
            'purchase' => $reportService->getPurchaseWidgetData($useCache),
            default => [],
        };
    }

    /**
     * Get all module widget data.
     *
     * @param bool $useCache Whether to use cached data
     * @return array All module widget data
     */
    public static function getAllModuleWidgets(bool $useCache = true): array
    {
        $reportService = new ReportService();
        return $reportService->getAllWidgetData($useCache);
    }

    /**
     * Clear dashboard cache.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        // Clear main data wrapper
        Cache::forget('dashboard_data_' . now()->format('Y-m-d-H'));

        // Clear individual metrics
        Cache::forget('dashboard_invoice_metrics');
        Cache::forget('dashboard_inventory_metrics');
        Cache::forget('dashboard_purchase_metrics');
        Cache::forget('dashboard_pending_actions');
        Cache::forget('dashboard_cash_flow');

        // Clear common variations for parametrized cache keys
        // Note: This covers standard dashboard usage. Custom reports might need their own clearing or shorter TTL.
        Cache::forget('dashboard_sales_metrics_' . now()->startOfMonth()->format('Y-m-d') . '_' . now()->endOfMonth()->format('Y-m-d'));
        Cache::forget('dashboard_top_customers_5');
        Cache::forget('dashboard_top_products_5');
        Cache::forget('dashboard_sales_chart_6');
        Cache::forget('dashboard_low_stock_5');
        Cache::forget('dashboard_low_stock_10');
        Cache::forget('dashboard_recent_activities_10');
        Cache::forget('dashboard_recent_orders_5');
        Cache::forget('dashboard_recent_invoices_5');

        ReportService::clearCache();
    }
}
