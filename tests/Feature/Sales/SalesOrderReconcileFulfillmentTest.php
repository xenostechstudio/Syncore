<?php

/**
 * Covers the sales-orders:reconcile-fulfillment artisan command. With
 * observer-driven recompute now in place, the regular flow never
 * produces drift — InvoiceItemObserver, DeliveryOrderItemObserver, and
 * the parent Invoice/DeliveryOrder observers keep the SO counters in
 * sync. The command exists for the cases observers can't catch:
 * raw DB::table()->insert(), legacy migrations, manual repair.
 *
 * To exercise the command we *manufacture* drift by writing to the SO
 * item via a raw query that bypasses observers, then assert the
 * command corrects it.
 */

use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function makeReconcileScenario(): array
{
    $user = User::factory()->create();

    $warehouse = Warehouse::create([
        'name' => 'WH '.uniqid(),
        'location' => 'Test',
    ]);

    $product = Product::create([
        'name' => 'Test Product '.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'product_type' => 'goods',
        'quantity' => 100,
        'cost_price' => 50,
        'selling_price' => 100,
        'status' => 'in_stock',
        'is_favorite' => false,
    ]);

    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Customer '.uniqid(),
        'address' => 'addr',
        'country' => 'ID',
        'status' => 'active',
    ]);

    $salesOrder = SalesOrder::create([
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'order_date' => now()->format('Y-m-d'),
        'status' => 'processing',
        'subtotal' => 500,
        'tax' => 0,
        'discount' => 0,
        'total' => 500,
        'shipping_address' => 'addr',
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $salesOrder->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 500,
    ]);

    return compact('user', 'warehouse', 'product', 'customer', 'salesOrder', 'soItem');
}

/**
 * Force the SO item's counters to a known-wrong value by writing
 * directly via the query builder — this bypasses Eloquent events, which
 * is exactly the situation reconcile-fulfillment exists to repair.
 */
function manufactureDrift(SalesOrderItem $item, int $invoiced = 0, int $delivered = 0): void
{
    DB::table('sales_order_items')
        ->where('id', $item->id)
        ->update([
            'quantity_invoiced' => $invoiced,
            'quantity_delivered' => $delivered,
        ]);
}

it('reconciles quantity_invoiced from invoice items linked by sales_order_id + product_id', function () {
    $s = makeReconcileScenario();

    $invoice = Invoice::create([
        'invoice_number' => 'INV-'.uniqid(),
        'customer_id' => $s['customer']->id,
        'sales_order_id' => $s['salesOrder']->id,
        'user_id' => $s['user']->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => 'draft',
        'subtotal' => 300,
        'tax' => 0,
        'discount' => 0,
        'total' => 300,
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'mirror',
        'quantity' => 3,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 300,
    ]);

    // Force drift after the observer-driven write.
    manufactureDrift($s['soItem'], invoiced: 0);
    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(0);

    $this->artisan('sales-orders:reconcile-fulfillment')
        ->assertExitCode(0);

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(3);
    expect((int) $s['soItem']->refresh()->quantity_delivered)->toBe(0);
});

it('reconciles quantity_delivered from DELIVERED delivery orders only', function () {
    $s = makeReconcileScenario();

    $pendingDo = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'pending',
        'shipping_address' => 'addr',
        'recipient_name' => 'someone',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $pendingDo->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 2,
        'quantity_delivered' => 0,
    ]);

    $deliveredDo = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'delivered',
        'shipping_address' => 'addr',
        'recipient_name' => 'someone',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $deliveredDo->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 4,
        'quantity_delivered' => 4,
    ]);

    manufactureDrift($s['soItem'], delivered: 0);

    $this->artisan('sales-orders:reconcile-fulfillment')
        ->assertExitCode(0);

    expect((int) $s['soItem']->refresh()->quantity_delivered)->toBe(4);
});

it('caps reconciled quantities at the SO item quantity (never over-counts)', function () {
    $s = makeReconcileScenario(); // soItem.quantity = 5

    $invoice = Invoice::create([
        'invoice_number' => 'INV-'.uniqid(),
        'customer_id' => $s['customer']->id,
        'sales_order_id' => $s['salesOrder']->id,
        'user_id' => $s['user']->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => 'draft',
        'subtotal' => 1000,
        'tax' => 0,
        'discount' => 0,
        'total' => 1000,
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'over1',
        'quantity' => 6,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 600,
    ]);
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'over2',
        'quantity' => 4,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 400,
    ]);

    // Observer would already cap at 5, but force drift to verify
    // the command's own capping logic.
    manufactureDrift($s['soItem'], invoiced: 99);

    $this->artisan('sales-orders:reconcile-fulfillment')
        ->assertExitCode(0);

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(5);
});

it('is idempotent — running twice produces the same result as running once', function () {
    $s = makeReconcileScenario();

    $invoice = Invoice::create([
        'invoice_number' => 'INV-'.uniqid(),
        'customer_id' => $s['customer']->id,
        'sales_order_id' => $s['salesOrder']->id,
        'user_id' => $s['user']->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => 'draft',
        'subtotal' => 200,
        'tax' => 0,
        'discount' => 0,
        'total' => 200,
    ]);
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'mirror',
        'quantity' => 2,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 200,
    ]);

    manufactureDrift($s['soItem'], invoiced: 0);

    $this->artisan('sales-orders:reconcile-fulfillment')->assertExitCode(0);
    $afterFirst = (int) $s['soItem']->refresh()->quantity_invoiced;

    $this->artisan('sales-orders:reconcile-fulfillment')->assertExitCode(0);
    $afterSecond = (int) $s['soItem']->refresh()->quantity_invoiced;

    expect($afterFirst)->toBe(2);
    expect($afterSecond)->toBe(2);
});

it('--dry-run reports drift but does not write', function () {
    $s = makeReconcileScenario();

    $invoice = Invoice::create([
        'invoice_number' => 'INV-'.uniqid(),
        'customer_id' => $s['customer']->id,
        'sales_order_id' => $s['salesOrder']->id,
        'user_id' => $s['user']->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => 'draft',
        'subtotal' => 100,
        'tax' => 0,
        'discount' => 0,
        'total' => 100,
    ]);
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'mirror',
        'quantity' => 1,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 100,
    ]);

    manufactureDrift($s['soItem'], invoiced: 0);

    $this->artisan('sales-orders:reconcile-fulfillment', ['--dry-run' => true])
        ->expectsOutputToContain('Would update')
        ->assertExitCode(0);

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(0);
});
