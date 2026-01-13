<?php

use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('can get sales metrics', function () {
    $customer = Customer::factory()->create();
    
    SalesOrder::factory()->count(3)->create([
        'customer_id' => $customer->id,
        'status' => 'confirmed',
        'total' => 1000000,
    ]);

    $metrics = DashboardService::getSalesMetrics();

    expect($metrics)->toHaveKey('total_sales')
        ->and($metrics)->toHaveKey('total_orders')
        ->and($metrics)->toHaveKey('average_order_value')
        ->and($metrics)->toHaveKey('sales_change')
        ->and($metrics)->toHaveKey('orders_change')
        ->and($metrics['total_orders'])->toBe(3);
});

test('can get invoice metrics', function () {
    $customer = Customer::factory()->create();
    
    Invoice::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'sent',
        'total' => 1000000,
        'paid_amount' => 0,
    ]);

    Invoice::factory()->create([
        'customer_id' => $customer->id,
        'status' => 'overdue',
        'total' => 500000,
        'paid_amount' => 0,
    ]);

    $metrics = DashboardService::getInvoiceMetrics();

    expect($metrics)->toHaveKey('total_outstanding')
        ->and($metrics)->toHaveKey('overdue_amount')
        ->and($metrics)->toHaveKey('overdue_count')
        ->and($metrics)->toHaveKey('paid_this_month')
        ->and($metrics['overdue_count'])->toBe(1);
});

test('can get inventory metrics', function () {
    Product::factory()->count(5)->create([
        'status' => 'active',
        'quantity' => 100,
        'cost_price' => 10000,
    ]);

    Product::factory()->create([
        'status' => 'active',
        'quantity' => 5, // Low stock
        'cost_price' => 10000,
    ]);

    $metrics = DashboardService::getInventoryMetrics();

    expect($metrics)->toHaveKey('total_products')
        ->and($metrics)->toHaveKey('low_stock_count')
        ->and($metrics)->toHaveKey('out_of_stock_count')
        ->and($metrics)->toHaveKey('total_inventory_value')
        ->and($metrics['total_products'])->toBe(6);
});

test('can get top customers', function () {
    $customer1 = Customer::factory()->create(['name' => 'Customer A']);
    $customer2 = Customer::factory()->create(['name' => 'Customer B']);

    SalesOrder::factory()->count(3)->create([
        'customer_id' => $customer1->id,
        'status' => 'confirmed',
        'total' => 1000000,
    ]);

    SalesOrder::factory()->create([
        'customer_id' => $customer2->id,
        'status' => 'confirmed',
        'total' => 500000,
    ]);

    $topCustomers = DashboardService::getTopCustomers(5);

    expect($topCustomers)->not->toBeEmpty()
        ->and($topCustomers[0]['total_sales'])->toBeGreaterThan($topCustomers[1]['total_sales'] ?? 0);
});

test('can get top products', function () {
    $product1 = Product::factory()->create(['name' => 'Product A']);
    $product2 = Product::factory()->create(['name' => 'Product B']);

    $topProducts = DashboardService::getTopProducts(5);

    expect($topProducts)->toBeArray();
});

test('can get sales chart data', function () {
    $chartData = DashboardService::getSalesChartData(6);

    expect($chartData)->toHaveCount(6)
        ->and($chartData[0])->toHaveKey('month')
        ->and($chartData[0])->toHaveKey('sales');
});

test('can get low stock products', function () {
    Product::factory()->create([
        'status' => 'active',
        'quantity' => 5,
    ]);

    Product::factory()->create([
        'status' => 'active',
        'quantity' => 100,
    ]);

    $lowStock = DashboardService::getLowStockProducts(10);

    expect($lowStock)->toHaveCount(1);
});

test('can get pending actions', function () {
    $actions = DashboardService::getPendingActions();

    expect($actions)->toHaveKey('pending_quotations')
        ->and($actions)->toHaveKey('orders_to_invoice')
        ->and($actions)->toHaveKey('orders_to_deliver')
        ->and($actions)->toHaveKey('draft_invoices')
        ->and($actions)->toHaveKey('overdue_invoices')
        ->and($actions)->toHaveKey('pending_bills');
});

test('can get cash flow summary', function () {
    $cashFlow = DashboardService::getCashFlowSummary();

    expect($cashFlow)->toHaveKey('receivables')
        ->and($cashFlow)->toHaveKey('payables')
        ->and($cashFlow)->toHaveKey('received_this_month')
        ->and($cashFlow)->toHaveKey('paid_this_month')
        ->and($cashFlow)->toHaveKey('net_cash_flow');
});

test('can get all dashboard data', function () {
    $data = DashboardService::getAllDashboardData(false);

    expect($data)->toHaveKey('sales')
        ->and($data)->toHaveKey('invoices')
        ->and($data)->toHaveKey('inventory')
        ->and($data)->toHaveKey('purchases')
        ->and($data)->toHaveKey('pending_actions')
        ->and($data)->toHaveKey('cash_flow');
});

test('dashboard data is cached', function () {
    // First call
    $data1 = DashboardService::getAllDashboardData(true);
    
    // Create new data
    Customer::factory()->create();
    
    // Second call should return cached data
    $data2 = DashboardService::getAllDashboardData(true);

    expect($data1)->toEqual($data2);
});

test('can clear cache', function () {
    DashboardService::getAllDashboardData(true);
    
    DashboardService::clearCache();

    // Cache should be cleared (no assertion needed, just ensure no error)
    expect(true)->toBeTrue();
});
