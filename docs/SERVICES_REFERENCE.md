# Services Reference

Documentation for backend services and their usage.

## Table of Contents

1. [Sales Services](#sales-services)
2. [Invoice Services](#invoice-services)
3. [Inventory Services](#inventory-services)
4. [Delivery Services](#delivery-services)
5. [Purchase Services](#purchase-services)
6. [CRM Services](#crm-services)
7. [HR Services](#hr-services)
8. [Report Services](#report-services)
9. [Utility Services](#utility-services)

---

## Sales Services

### SalesOrderService

Handles sales order business logic.

```php
use App\Services\SalesOrderService;

// Create a new sales order
$order = SalesOrderService::create([
    'customer_id' => 1,
    'order_date' => now(),
    'notes' => 'Rush order',
], $items);

// Confirm a quotation
SalesOrderService::confirm($order);

// Create invoice from order
$invoice = SalesOrderService::createInvoice($order);

// Create delivery from order
$delivery = SalesOrderService::createDelivery($order);

// Cancel order
SalesOrderService::cancel($order, 'Customer requested cancellation');
```

---

## Invoice Services

### InvoiceService

Manages invoice operations.

```php
use App\Services\InvoiceService;

// Create invoice
$invoice = InvoiceService::create([
    'customer_id' => 1,
    'invoice_date' => now(),
    'due_date' => now()->addDays(30),
], $items);

// Send invoice
InvoiceService::send($invoice);

// Record payment
InvoiceService::recordPayment($invoice, [
    'amount' => 1000000,
    'payment_method' => 'bank_transfer',
    'payment_date' => now(),
    'reference' => 'TRF-001',
]);

// Mark as paid
InvoiceService::markPaid($invoice);

// Check overdue invoices
InvoiceService::checkOverdue();
```

---

## Inventory Services

### InventoryService

Manages stock operations.

```php
use App\Services\InventoryService;

// Increase stock
InventoryService::increaseStock($productId, $warehouseId, $quantity, 'Purchase received');

// Decrease stock
InventoryService::decreaseStock($productId, $warehouseId, $quantity, 'Sold');

// Transfer stock between warehouses
InventoryService::transfer($productId, $fromWarehouseId, $toWarehouseId, $quantity);

// Adjust stock
InventoryService::adjust($productId, $warehouseId, $newQuantity, 'Inventory count adjustment');

// Get stock level
$stock = InventoryService::getStock($productId, $warehouseId);

// Get total stock across warehouses
$total = InventoryService::getTotalStock($productId);

// Check low stock
$lowStockProducts = InventoryService::getLowStockProducts($threshold);
```

---

## Delivery Services

### DeliveryService

Handles delivery operations.

```php
use App\Services\DeliveryService;

// Create delivery order
$delivery = DeliveryService::create([
    'sales_order_id' => 1,
    'scheduled_date' => now()->addDays(2),
    'shipping_address' => '123 Main St',
], $items);

// Mark as ready
DeliveryService::markReady($delivery);

// Ship delivery
DeliveryService::ship($delivery, [
    'tracking_number' => 'TRK123456',
    'carrier' => 'JNE',
]);

// Complete delivery
DeliveryService::complete($delivery);

// Cancel delivery
DeliveryService::cancel($delivery, 'Customer cancelled');
```

---

## Purchase Services

### PurchaseService

Manages purchase operations.

```php
use App\Services\PurchaseService;

$service = new PurchaseService();

// Create RFQ
$rfq = $service->createRfq([
    'supplier_id' => 1,
    'order_date' => now(),
    'expected_arrival' => now()->addDays(7),
], $items);

// Send RFQ
$service->sendRfq($rfq);

// Confirm as Purchase Order
$service->confirmOrder($rfq);

// Mark as received
$service->markReceived($rfq);

// Create vendor bill
$bill = $service->createBill($rfq);

// Cancel
$service->cancel($rfq, 'Supplier cannot fulfill');
```

---

## CRM Services

### CRMService

Manages CRM operations.

```php
use App\Services\CRMService;

$service = new CRMService();

// Create lead
$lead = $service->createLead([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'source' => 'website',
]);

// Update lead status
$service->updateLeadStatus($lead, 'qualified');

// Convert lead to customer
$customer = $service->convertLeadToCustomer($lead);

// Create opportunity
$opportunity = $service->createOpportunity([
    'name' => 'Big Deal',
    'customer_id' => $customer->id,
    'pipeline_id' => 1,
    'expected_revenue' => 50000000,
]);

// Move to next stage
$service->moveToNextStage($opportunity);

// Mark as won
$service->markOpportunityAsWon($opportunity, $salesOrderId);

// Mark as lost
$service->markOpportunityAsLost($opportunity, 'Lost to competitor');

// Log activity
$service->logActivity('call', 'Follow-up call', $opportunity, 'Discussed pricing');

// Get pipeline stats
$stats = $service->getPipelineStats();
```

---

## HR Services

### PayrollService

Manages payroll operations.

```php
use App\Services\PayrollService;

$service = new PayrollService();

// Create payroll period
$period = $service->createPeriod([
    'name' => 'January 2026',
    'start_date' => '2026-01-01',
    'end_date' => '2026-01-31',
    'payment_date' => '2026-01-25',
]);

// Generate payroll items for all employees
$count = $service->generatePayrollItems($period);

// Approve payroll
$service->approve($period);

// Start processing
$service->startProcessing($period);

// Mark as paid
$service->markPaid($period);

// Cancel
$service->cancel($period, 'Error in calculation');

// Reset to draft
$service->resetToDraft($period);
```

---

## Report Services

### ReportService

Unified reporting service.

```php
use App\Services\Reports\ReportService;

$service = new ReportService();

// Get widget data for dashboard
$salesWidget = $service->getSalesWidgetData();
$inventoryWidget = $service->getInventoryWidgetData();
$invoicingWidget = $service->getInvoicingWidgetData();
$hrWidget = $service->getHRWidgetData();
$crmWidget = $service->getCRMWidgetData();
$purchaseWidget = $service->getPurchaseWidgetData();

// Get all widgets
$allWidgets = $service->getAllWidgetData();

// Generate custom report
$report = $service->generateCustomReport(
    'sales',
    $startDate,
    $endDate,
    ['group_by' => 'month', 'limit' => 20]
);

// Clear cache
ReportService::clearCache();
```

### Individual Report Services

```php
use App\Services\Reports\SalesReportService;
use App\Services\Reports\InventoryReportService;
use App\Services\Reports\InvoiceReportService;
use App\Services\Reports\HRReportService;
use App\Services\Reports\CRMReportService;
use App\Services\Reports\PurchaseReportService;

// Sales reports
$salesReport = new SalesReportService();
$salesByPeriod = $salesReport->getSalesByPeriod($startDate, $endDate, 'month');
$salesByCustomer = $salesReport->getSalesByCustomer($startDate, $endDate, 10);
$salesByProduct = $salesReport->getSalesByProduct($startDate, $endDate, 10);

// Inventory reports
$inventoryReport = new InventoryReportService();
$valuation = $inventoryReport->getStockValuation($warehouseId);
$lowStock = $inventoryReport->getLowStockProducts(10);

// Invoice reports
$invoiceReport = new InvoiceReportService();
$aging = $invoiceReport->getAgingReport();
$revenue = $invoiceReport->getRevenueByPeriod($startDate, $endDate, 'month');

// HR reports
$hrReport = new HRReportService();
$turnover = $hrReport->getTurnoverRate($startDate, $endDate);
$leaveAnalysis = $hrReport->getLeaveAnalysis($startDate, $endDate);

// CRM reports
$crmReport = new CRMReportService();
$pipeline = $crmReport->getPipelineAnalysis();
$forecast = $crmReport->getSalesForecast(3);

// Purchase reports
$purchaseReport = new PurchaseReportService();
$billAging = $purchaseReport->getBillAgingReport();
$supplierPerformance = $purchaseReport->getSupplierPerformance($startDate, $endDate);
```

---

## Utility Services

### CurrencyService

Multi-currency support.

```php
use App\Services\CurrencyService;

// Convert between currencies
$usdAmount = CurrencyService::convert(1000000, 'IDR', 'USD');

// Convert to base currency
$baseAmount = CurrencyService::toBase(100, 'USD');

// Convert from base currency
$foreignAmount = CurrencyService::fromBase(1000000, 'USD');

// Format with currency
$formatted = CurrencyService::format(1000000, 'IDR'); // "Rp 1.000.000"

// Get exchange rate
$rate = CurrencyService::getRate('IDR', 'USD');

// Update exchange rate
CurrencyService::updateRate('IDR', 'USD', 0.000063, now(), 'api');

// Get active currencies
$currencies = CurrencyService::getActiveCurrencies();

// Get default currency
$default = CurrencyService::getDefaultCurrency();
```

### CacheService

Centralized caching.

```php
use App\Services\CacheService;

// Remember value
$value = CacheService::remember('key', fn() => expensiveOperation(), 3600);

// Cache model
$customer = CacheService::model(Customer::class, 1, fn() => Customer::find(1));

// Cache collection
$products = CacheService::collection('active_products', fn() => Product::active()->get());

// Cache dashboard data
$data = CacheService::dashboard('sales', fn() => getSalesData());

// Cache report
$report = CacheService::report('sales', $params, fn() => generateReport($params));

// Invalidate caches
CacheService::invalidateModel($customer);
CacheService::invalidateDashboard('sales');
CacheService::invalidateReports('sales');
CacheService::invalidateAll();

// Get stats
$stats = CacheService::getStats();
```

### ActivityLogService

Audit trail and activity logging.

```php
use App\Services\ActivityLogService;

// Log activity
ActivityLogService::log('custom_action', $model, 'Description', ['key' => 'value']);

// Log model events
ActivityLogService::logCreated($model);
ActivityLogService::logUpdated($model, $oldValues);
ActivityLogService::logDeleted($model);
ActivityLogService::logStatusChanged($model, 'draft', 'confirmed');

// Query activities
$activities = ActivityLogService::getActivitiesFor($model, 20);
$userActivities = ActivityLogService::getActivitiesByUser($userId, 20);

// Search with filters
$results = ActivityLogService::search([
    'user_id' => 1,
    'action' => 'created',
    'model_type' => 'Invoice',
    'date_from' => '2026-01-01',
    'date_to' => '2026-01-31',
    'search' => 'keyword',
], 50);

// Get statistics
$stats = ActivityLogService::getStatistics($startDate, $endDate);

// Get timeline
$timeline = ActivityLogService::getTimeline($model, 50);

// Compare versions
$diff = ActivityLogService::compareVersions($logId1, $logId2);

// Export
$export = ActivityLogService::export($filters, 1000);

// Cleanup old logs
$deleted = ActivityLogService::cleanup(90); // Keep 90 days
```

### NotificationService

System notifications.

```php
use App\Services\NotificationService;

// Inventory notifications
NotificationService::lowStock($product, $userIds);
NotificationService::outOfStock($product, $userIds);

// Invoice notifications
NotificationService::invoiceOverdue($invoice, $userIds);
NotificationService::paymentReceived($invoice, $amount, $userIds);
NotificationService::invoicePaid($invoice, $userIds);

// Vendor bill notifications
NotificationService::billDueSoon($bill, $daysUntilDue, $userIds);
NotificationService::billOverdue($bill, $userIds);

// Approval notifications
NotificationService::approvalRequired($model, 'leave_request', 'LR-001', $userIds);

// Custom notification
NotificationService::custom(
    $userIds,
    'custom_type',
    'Title',
    'Message',
    $actionUrl,
    $model,
    'icon-name',
    'color'
);

// Flexible notification (used by event listeners)
NotificationService::create(
    type: 'opportunity_won',
    title: 'Opportunity Won!',
    message: 'Deal closed successfully',
    notifiable: $opportunity,
    userId: $userId,
    data: ['key' => 'value']
);

// Mark as read
NotificationService::markAllAsRead($userId);

// Get unread count
$count = NotificationService::getUnreadCount($userId);
```

### PerformanceService

Performance monitoring.

```php
use App\Services\PerformanceService;

// Monitor queries
PerformanceService::startMonitoring();
// ... your code ...
$results = PerformanceService::stopMonitoring();

// Log slow queries
PerformanceService::logSlowQueries(100); // Log queries > 100ms

// Get database stats
$dbStats = PerformanceService::getDatabaseStats();

// Get cache stats
$cacheStats = PerformanceService::getCacheStats();

// Get health metrics
$health = PerformanceService::getHealthMetrics();
```

### DashboardService

Dashboard data aggregation.

```php
use App\Services\DashboardService;

// Get all dashboard data
$data = DashboardService::getAllDashboardData($useCache);

// Get specific metrics
$sales = DashboardService::getSalesMetrics($startDate, $endDate);
$invoices = DashboardService::getInvoiceMetrics();
$inventory = DashboardService::getInventoryMetrics();
$purchases = DashboardService::getPurchaseMetrics();
$pendingActions = DashboardService::getPendingActions();
$cashFlow = DashboardService::getCashFlowSummary();

// Get lists
$topCustomers = DashboardService::getTopCustomers(5);
$topProducts = DashboardService::getTopProducts(5);
$lowStock = DashboardService::getLowStockProducts(10);
$recentActivities = DashboardService::getRecentActivities(10);
$recentOrders = DashboardService::getRecentOrders(5);
$recentInvoices = DashboardService::getRecentInvoices(5);

// Get chart data
$salesChart = DashboardService::getSalesChartData(6);

// Get module widget data
$moduleData = DashboardService::getModuleWidgetData('sales');
$allModules = DashboardService::getAllModuleWidgets();

// Clear cache
DashboardService::clearCache();
```

### ExportService

Data export functionality.

```php
use App\Services\ExportService;

// Export to Excel
return ExportService::toExcel(CustomersExport::class, 'customers', $selectedIds);

// Export to CSV
return ExportService::toCsv(ProductsExport::class, 'products');

// Export to PDF
return ExportService::toPdf('pdf.report', $data, 'report', 'landscape');

// Stream PDF
return ExportService::streamPdf('pdf.invoice', $data, 'invoice');

// Export to JSON
return ExportService::toJson($data, 'export');

// Generate filename
$filename = ExportService::generateFilename('customers', 'xlsx');
```
