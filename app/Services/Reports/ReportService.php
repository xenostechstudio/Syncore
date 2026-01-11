<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Unified Report Service
 * 
 * Aggregates all report services and provides dashboard widget data
 * with caching support for improved performance.
 */
class ReportService
{
    protected const CACHE_TTL = 300; // 5 minutes

    protected SalesReportService $salesReport;
    protected InventoryReportService $inventoryReport;
    protected InvoiceReportService $invoiceReport;
    protected HRReportService $hrReport;
    protected CRMReportService $crmReport;
    protected PurchaseReportService $purchaseReport;

    public function __construct()
    {
        $this->salesReport = new SalesReportService();
        $this->inventoryReport = new InventoryReportService();
        $this->invoiceReport = new InvoiceReportService();
        $this->hrReport = new HRReportService();
        $this->crmReport = new CRMReportService();
        $this->purchaseReport = new PurchaseReportService();
    }

    /**
     * Get sales dashboard widget data.
     */
    public function getSalesWidgetData(bool $useCache = true): array
    {
        $cacheKey = 'widget_sales_' . now()->format('Y-m-d-H');

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $data = [
            'summary' => $this->salesReport->getSummary($startDate, $endDate),
            'chart' => $this->salesReport->getSalesByPeriod(
                now()->subMonths(6)->startOfMonth(),
                now()->endOfMonth(),
                'month'
            ),
            'top_customers' => $this->salesReport->getSalesByCustomer($startDate, $endDate, 5),
            'top_products' => $this->salesReport->getSalesByProduct($startDate, $endDate, 5),
        ];

        if ($useCache) {
            Cache::put($cacheKey, $data, self::CACHE_TTL);
        }

        return $data;
    }

    /**
     * Get inventory dashboard widget data.
     */
    public function getInventoryWidgetData(bool $useCache = true): array
    {
        $cacheKey = 'widget_inventory_' . now()->format('Y-m-d-H');

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $data = [
            'summary' => $this->inventoryReport->getSummary(),
            'low_stock' => $this->inventoryReport->getLowStockProducts(10),
            'by_warehouse' => $this->inventoryReport->getStockByWarehouse(),
            'out_of_stock' => $this->inventoryReport->getOutOfStockProducts(),
        ];

        if ($useCache) {
            Cache::put($cacheKey, $data, self::CACHE_TTL);
        }

        return $data;
    }

    /**
     * Get invoicing dashboard widget data.
     */
    public function getInvoicingWidgetData(bool $useCache = true): array
    {
        $cacheKey = 'widget_invoicing_' . now()->format('Y-m-d-H');

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $data = [
            'summary' => $this->invoiceReport->getSummary($startDate, $endDate),
            'aging' => $this->invoiceReport->getAgingReport(),
            'revenue_chart' => $this->invoiceReport->getRevenueByPeriod(
                now()->subMonths(6)->startOfMonth(),
                now()->endOfMonth(),
                'month'
            ),
            'payment_methods' => $this->invoiceReport->getPaymentsByMethod($startDate, $endDate),
        ];

        if ($useCache) {
            Cache::put($cacheKey, $data, self::CACHE_TTL);
        }

        return $data;
    }


    /**
     * Get HR dashboard widget data.
     */
    public function getHRWidgetData(bool $useCache = true): array
    {
        $cacheKey = 'widget_hr_' . now()->format('Y-m-d-H');

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $data = [
            'summary' => $this->hrReport->getSummary(),
            'by_department' => $this->hrReport->getEmployeesByDepartment(),
            'leave_analysis' => $this->hrReport->getLeaveAnalysis($startDate, $endDate),
            'turnover' => $this->hrReport->getTurnoverRate(
                now()->subYear()->startOfYear(),
                now()
            ),
        ];

        if ($useCache) {
            Cache::put($cacheKey, $data, self::CACHE_TTL);
        }

        return $data;
    }

    /**
     * Get CRM dashboard widget data.
     */
    public function getCRMWidgetData(bool $useCache = true): array
    {
        $cacheKey = 'widget_crm_' . now()->format('Y-m-d-H');

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $data = [
            'summary' => $this->crmReport->getSummary(),
            'pipeline' => $this->crmReport->getPipelineAnalysis(),
            'lead_funnel' => $this->crmReport->getLeadConversionFunnel($startDate, $endDate),
            'win_loss' => $this->crmReport->getWinLossAnalysis($startDate, $endDate),
            'forecast' => $this->crmReport->getSalesForecast(3),
            'leads_by_source' => $this->crmReport->getLeadsBySource($startDate, $endDate),
        ];

        if ($useCache) {
            Cache::put($cacheKey, $data, self::CACHE_TTL);
        }

        return $data;
    }

    /**
     * Get Purchase dashboard widget data.
     */
    public function getPurchaseWidgetData(bool $useCache = true): array
    {
        $cacheKey = 'widget_purchase_' . now()->format('Y-m-d-H');

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        $data = [
            'summary' => $this->purchaseReport->getSummary($startDate, $endDate),
            'by_supplier' => $this->purchaseReport->getPurchasesBySupplier($startDate, $endDate, 5),
            'bill_aging' => $this->purchaseReport->getBillAgingReport(),
            'status_breakdown' => $this->purchaseReport->getOrderStatusBreakdown($startDate, $endDate),
            'chart' => $this->purchaseReport->getPurchasesByPeriod(
                now()->subMonths(6)->startOfMonth(),
                now()->endOfMonth(),
                'month'
            ),
        ];

        if ($useCache) {
            Cache::put($cacheKey, $data, self::CACHE_TTL);
        }

        return $data;
    }

    /**
     * Get all widget data for main dashboard.
     */
    public function getAllWidgetData(bool $useCache = true): array
    {
        return [
            'sales' => $this->getSalesWidgetData($useCache),
            'inventory' => $this->getInventoryWidgetData($useCache),
            'invoicing' => $this->getInvoicingWidgetData($useCache),
            'hr' => $this->getHRWidgetData($useCache),
            'crm' => $this->getCRMWidgetData($useCache),
            'purchase' => $this->getPurchaseWidgetData($useCache),
        ];
    }

    /**
     * Generate custom report with flexible parameters.
     */
    public function generateCustomReport(
        string $reportType,
        Carbon $startDate,
        Carbon $endDate,
        array $options = []
    ): array {
        return match ($reportType) {
            'sales' => [
                'summary' => $this->salesReport->getSummary($startDate, $endDate),
                'by_period' => $this->salesReport->getSalesByPeriod($startDate, $endDate, $options['group_by'] ?? 'day'),
                'by_customer' => $this->salesReport->getSalesByCustomer($startDate, $endDate, $options['limit'] ?? 10),
                'by_product' => $this->salesReport->getSalesByProduct($startDate, $endDate, $options['limit'] ?? 10),
                'salesperson' => $this->salesReport->getSalespersonPerformance($startDate, $endDate),
            ],
            'inventory' => [
                'summary' => $this->inventoryReport->getSummary(),
                'valuation' => $this->inventoryReport->getStockValuation($options['warehouse_id'] ?? null),
                'low_stock' => $this->inventoryReport->getLowStockProducts($options['threshold'] ?? 10),
                'by_warehouse' => $this->inventoryReport->getStockByWarehouse(),
            ],
            'invoicing' => [
                'summary' => $this->invoiceReport->getSummary($startDate, $endDate),
                'by_period' => $this->invoiceReport->getRevenueByPeriod($startDate, $endDate, $options['group_by'] ?? 'month'),
                'aging' => $this->invoiceReport->getAgingReport(),
                'payment_methods' => $this->invoiceReport->getPaymentsByMethod($startDate, $endDate),
            ],
            'hr' => [
                'summary' => $this->hrReport->getSummary(),
                'by_department' => $this->hrReport->getEmployeesByDepartment(),
                'leave_analysis' => $this->hrReport->getLeaveAnalysis($startDate, $endDate),
                'payroll' => $this->hrReport->getPayrollSummary($startDate, $endDate),
                'turnover' => $this->hrReport->getTurnoverRate($startDate, $endDate),
            ],
            'crm' => [
                'summary' => $this->crmReport->getSummary(),
                'pipeline' => $this->crmReport->getPipelineAnalysis(),
                'lead_funnel' => $this->crmReport->getLeadConversionFunnel($startDate, $endDate),
                'win_loss' => $this->crmReport->getWinLossAnalysis($startDate, $endDate),
                'salesperson' => $this->crmReport->getSalespersonPerformance($startDate, $endDate),
            ],
            'purchase' => [
                'summary' => $this->purchaseReport->getSummary($startDate, $endDate),
                'by_period' => $this->purchaseReport->getPurchasesByPeriod($startDate, $endDate, $options['group_by'] ?? 'month'),
                'by_supplier' => $this->purchaseReport->getPurchasesBySupplier($startDate, $endDate, $options['limit'] ?? 10),
                'bill_aging' => $this->purchaseReport->getBillAgingReport(),
            ],
            default => [],
        };
    }

    /**
     * Clear all widget caches.
     */
    public static function clearCache(): void
    {
        $hour = now()->format('Y-m-d-H');
        Cache::forget("widget_sales_{$hour}");
        Cache::forget("widget_inventory_{$hour}");
        Cache::forget("widget_invoicing_{$hour}");
        Cache::forget("widget_hr_{$hour}");
        Cache::forget("widget_crm_{$hour}");
        Cache::forget("widget_purchase_{$hour}");
    }
}
