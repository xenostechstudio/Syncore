<?php

namespace App\Services\Reports;

use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\VendorBill;
use App\Models\Purchase\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Purchase Report Service
 * 
 * Provides purchase analytics including supplier analysis,
 * purchase trends, and vendor bill aging.
 */
class PurchaseReportService
{
    /**
     * Get purchases by period.
     */
    public function getPurchasesByPeriod(Carbon $startDate, Carbon $endDate, string $groupBy = 'month'): array
    {
        $dbFormat = match ($groupBy) {
            'day' => "TO_CHAR(order_date, 'YYYY-MM-DD')",
            'week' => "TO_CHAR(order_date, 'IYYY-IW')",
            'month' => "TO_CHAR(order_date, 'YYYY-MM')",
            'year' => "TO_CHAR(order_date, 'YYYY')",
            default => "TO_CHAR(order_date, 'YYYY-MM')",
        };

        return PurchaseRfq::query()
            ->selectRaw("{$dbFormat} as period, COUNT(*) as order_count, SUM(total) as total_amount")
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['purchase_order', 'received', 'billed'])
            ->groupByRaw($dbFormat)
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    /**
     * Get purchases by supplier.
     */
    public function getPurchasesBySupplier(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        return PurchaseRfq::query()
            ->select('supplier_id')
            ->selectRaw('COUNT(*) as order_count, SUM(total) as total_amount')
            ->with('supplier:id,name,email')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['purchase_order', 'received', 'billed'])
            ->groupBy('supplier_id')
            ->orderByDesc('total_amount')
            ->limit($limit)
            ->get()
            ->map(fn($item) => [
                'supplier_id' => $item->supplier_id,
                'supplier_name' => $item->supplier?->name ?? 'Unknown',
                'order_count' => $item->order_count,
                'total_amount' => $item->total_amount,
            ])
            ->toArray();
    }

    /**
     * Get purchases by product category.
     */
    public function getPurchasesByCategory(Carbon $startDate, Carbon $endDate): array
    {
        return DB::table('purchase_rfq_items')
            ->join('purchase_rfqs', 'purchase_rfq_items.purchase_rfq_id', '=', 'purchase_rfqs.id')
            ->join('products', 'purchase_rfq_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as category')
            ->selectRaw('COUNT(DISTINCT purchase_rfqs.id) as order_count')
            ->selectRaw('SUM(purchase_rfq_items.quantity) as total_quantity')
            ->selectRaw('SUM(purchase_rfq_items.total) as total_amount')
            ->whereBetween('purchase_rfqs.order_date', [$startDate, $endDate])
            ->whereIn('purchase_rfqs.status', ['purchase_order', 'received', 'billed'])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_amount')
            ->get()
            ->map(fn($item) => [
                'category' => $item->category ?? 'Uncategorized',
                'order_count' => $item->order_count,
                'total_quantity' => $item->total_quantity,
                'total_amount' => $item->total_amount,
            ])
            ->toArray();
    }


    /**
     * Get vendor bill aging report.
     */
    public function getBillAgingReport(): array
    {
        $today = now();

        $aging = [
            'current' => ['count' => 0, 'amount' => 0],
            '1_30' => ['count' => 0, 'amount' => 0],
            '31_60' => ['count' => 0, 'amount' => 0],
            '61_90' => ['count' => 0, 'amount' => 0],
            'over_90' => ['count' => 0, 'amount' => 0],
        ];

        $bills = VendorBill::query()
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->get();

        foreach ($bills as $bill) {
            $dueDate = $bill->due_date ?? $bill->bill_date;
            $daysOverdue = $today->diffInDays($dueDate, false);
            $amountDue = $bill->total - ($bill->paid_amount ?? 0);

            if ($daysOverdue >= 0) {
                $aging['current']['count']++;
                $aging['current']['amount'] += $amountDue;
            } elseif ($daysOverdue >= -30) {
                $aging['1_30']['count']++;
                $aging['1_30']['amount'] += $amountDue;
            } elseif ($daysOverdue >= -60) {
                $aging['31_60']['count']++;
                $aging['31_60']['amount'] += $amountDue;
            } elseif ($daysOverdue >= -90) {
                $aging['61_90']['count']++;
                $aging['61_90']['amount'] += $amountDue;
            } else {
                $aging['over_90']['count']++;
                $aging['over_90']['amount'] += $amountDue;
            }
        }

        return $aging;
    }

    /**
     * Get supplier performance metrics.
     */
    public function getSupplierPerformance(Carbon $startDate, Carbon $endDate): array
    {
        return Supplier::query()
            ->select('suppliers.*')
            ->selectRaw('COUNT(purchase_rfqs.id) as order_count')
            ->selectRaw('COALESCE(SUM(purchase_rfqs.total), 0) as total_spent')
            ->selectRaw("COUNT(CASE WHEN purchase_rfqs.status = 'received' AND purchase_rfqs.expected_arrival >= purchase_rfqs.updated_at THEN 1 END) as on_time_count")
            ->leftJoin('purchase_rfqs', function ($join) use ($startDate, $endDate) {
                $join->on('suppliers.id', '=', 'purchase_rfqs.supplier_id')
                    ->whereBetween('purchase_rfqs.order_date', [$startDate, $endDate])
                    ->whereIn('purchase_rfqs.status', ['purchase_order', 'received', 'billed']);
            })
            ->groupBy('suppliers.id')
            ->having('order_count', '>', 0)
            ->orderByDesc('total_spent')
            ->get()
            ->map(fn($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'order_count' => $supplier->order_count,
                'total_spent' => $supplier->total_spent,
                'on_time_delivery_rate' => $supplier->order_count > 0 
                    ? round(($supplier->on_time_count / $supplier->order_count) * 100, 1) 
                    : 0,
            ])
            ->toArray();
    }

    /**
     * Get purchase order status breakdown.
     */
    public function getOrderStatusBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        return PurchaseRfq::query()
            ->select('status')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('COALESCE(SUM(total), 0) as total_amount')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->map(fn($item) => [
                'status' => ucfirst(str_replace('_', ' ', $item->status)),
                'count' => $item->count,
                'total_amount' => $item->total_amount,
            ])
            ->toArray();
    }

    /**
     * Get purchase summary metrics.
     */
    public function getSummary(Carbon $startDate, Carbon $endDate): array
    {
        $orders = PurchaseRfq::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['purchase_order', 'received', 'billed']);

        $totalOrders = (clone $orders)->count();
        $totalAmount = (clone $orders)->sum('total');
        $avgOrderValue = $totalOrders > 0 ? $totalAmount / $totalOrders : 0;

        $pendingBills = VendorBill::whereIn('status', ['pending', 'partial'])
            ->selectRaw('SUM(total - COALESCE(paid_amount, 0)) as amount')
            ->value('amount') ?? 0;

        $overdueBills = VendorBill::where('status', 'overdue')
            ->selectRaw('SUM(total - COALESCE(paid_amount, 0)) as amount')
            ->value('amount') ?? 0;

        return [
            'total_orders' => $totalOrders,
            'total_amount' => $totalAmount,
            'avg_order_value' => round($avgOrderValue, 2),
            'pending_bills' => $pendingBills,
            'overdue_bills' => $overdueBills,
            'active_suppliers' => Supplier::where('status', 'active')->count(),
        ];
    }
}
