<?php

/**
 * Observer-driven recompute of SalesOrderItem.quantity_invoiced /
 * quantity_delivered from related Invoice + DeliveryOrder rows. These
 * tests pin down what the production write paths now rely on: that no
 * one has to call ->increment('quantity_invoiced', X) manually because
 * the observers do it from the underlying invoice / delivery data.
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

function makeObserverScenario(int $orderedQty = 5): array
{
    $user = User::factory()->create();
    $warehouse = Warehouse::create(['name' => 'WH '.uniqid(), 'location' => 'Test']);
    $product = Product::create([
        'name' => 'P '.uniqid(),
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
        'name' => 'C '.uniqid(),
        'address' => 'a',
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
        'quantity' => $orderedQty,
        'unit_price' => 100,
        'discount' => 0,
        'total' => $orderedQty * 100,
    ]);
    return compact('user', 'warehouse', 'product', 'customer', 'salesOrder', 'soItem');
}

function makeInvoiceFor(array $s, string $status = 'draft'): Invoice
{
    return Invoice::create([
        'invoice_number' => 'INV-'.uniqid(),
        'customer_id' => $s['customer']->id,
        'sales_order_id' => $s['salesOrder']->id,
        'user_id' => $s['user']->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => $status,
        'subtotal' => 100,
        'tax' => 0,
        'discount' => 0,
        'total' => 100,
    ]);
}

it('creating an InvoiceItem bumps SalesOrderItem.quantity_invoiced via the observer', function () {
    $s = makeObserverScenario();
    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(0);

    $invoice = makeInvoiceFor($s);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'mirror',
        'quantity' => 3,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 300,
    ]);

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(3);
});

it('deleting an InvoiceItem recomputes SalesOrderItem.quantity_invoiced downward', function () {
    $s = makeObserverScenario();
    $invoice = makeInvoiceFor($s);

    $item = InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'mirror',
        'quantity' => 2,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 200,
    ]);

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(2);

    $item->delete();

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(0);
});

it('flipping an Invoice status to cancelled zeroes the counter; uncancel brings it back', function () {
    $s = makeObserverScenario();
    $invoice = makeInvoiceFor($s, 'sent');

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'mirror',
        'quantity' => 4,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 400,
    ]);

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(4);

    $invoice->update(['status' => 'cancelled']);
    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(0);

    $invoice->update(['status' => 'sent']);
    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(4);
});

it('a DeliveryOrderItem on a pending DO does NOT touch quantity_delivered', function () {
    $s = makeObserverScenario();

    $pendingDo = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'pending',
        'shipping_address' => 'addr',
        'recipient_name' => 'r',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $pendingDo->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 5,
        'quantity_delivered' => 5,
    ]);

    expect((int) $s['soItem']->refresh()->quantity_delivered)->toBe(0);
});

it('flipping a DO from in_transit to delivered bumps quantity_delivered', function () {
    $s = makeObserverScenario();

    $do = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'in_transit',
        'shipping_address' => 'addr',
        'recipient_name' => 'r',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $do->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 5,
        'quantity_delivered' => 5,
    ]);

    expect((int) $s['soItem']->refresh()->quantity_delivered)->toBe(0);

    $do->update(['status' => 'delivered']);

    expect((int) $s['soItem']->refresh()->quantity_delivered)->toBe(5);
});

it('cancelling a delivered DO zeroes quantity_delivered again', function () {
    $s = makeObserverScenario();

    $do = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'delivered',
        'shipping_address' => 'addr',
        'recipient_name' => 'r',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $do->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 5,
        'quantity_delivered' => 5,
    ]);
    expect((int) $s['soItem']->refresh()->quantity_delivered)->toBe(5);

    $do->update(['status' => 'cancelled']);
    expect((int) $s['soItem']->refresh()->quantity_delivered)->toBe(0);
});

it('soft-deleting then restoring an Invoice drops then restores the counter', function () {
    $s = makeObserverScenario();
    $invoice = makeInvoiceFor($s, 'sent');
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'mirror',
        'quantity' => 3,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 300,
    ]);

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(3);

    $invoice->delete();
    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(0);

    $invoice->restore();
    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(3);
});

it('locks the SO to DONE once every line is fully paid AND fully delivered', function () {
    $s = makeObserverScenario(orderedQty: 5);

    // Confirm the SO so it lands in SALES_ORDER (the gate state for lock()).
    $s['salesOrder']->update(['status' => 'processing']);

    $invoice = makeInvoiceFor($s, 'sent');
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'full',
        'quantity' => 5,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 500,
    ]);

    // Fully invoiced but not yet delivered — must NOT lock.
    expect($s['salesOrder']->fresh()->status)->toBe('processing');

    $do = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'in_transit',
        'shipping_address' => 'addr',
        'recipient_name' => 'r',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $do->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 5,
        'quantity_delivered' => 5,
    ]);

    // DO not yet delivered — still NOT locked.
    expect($s['salesOrder']->fresh()->status)->toBe('processing');

    $do->update(['status' => 'delivered']);

    // Fully invoiced + fully delivered, but the invoice is still 'sent'
    // (unpaid) — the money side isn't settled, so it must NOT lock.
    expect($s['salesOrder']->fresh()->status)->toBe('processing');

    // Settling the invoice is the last event needed.
    $invoice->update(['status' => 'paid']);

    expect($s['salesOrder']->fresh()->status)->toBe('delivered'); // = SalesOrderState::DONE
});

it('does NOT lock an SO that is fully invoiced and fully delivered but unpaid', function () {
    $s = makeObserverScenario(orderedQty: 4);
    $s['salesOrder']->update(['status' => 'processing']);

    $invoice = makeInvoiceFor($s, 'sent');
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'full',
        'quantity' => 4,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 400,
    ]);

    $do = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'delivered',
        'shipping_address' => 'addr',
        'recipient_name' => 'r',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $do->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 4,
        'quantity_delivered' => 4,
    ]);

    // Goods are out and the full quantity is invoiced — but the invoice
    // is 'sent', not 'paid'. The SO stays open until it's collected.
    expect($s['salesOrder']->fresh()->status)->toBe('processing');
    expect($s['salesOrder']->fresh()->isFullyDelivered())->toBeTrue();
});

it('does NOT lock a partially-fulfilled SO', function () {
    $s = makeObserverScenario(orderedQty: 5);
    $s['salesOrder']->update(['status' => 'processing']);

    $invoice = makeInvoiceFor($s, 'sent');
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'half',
        'quantity' => 3, // partial
        'unit_price' => 100,
        'discount' => 0,
        'total' => 300,
    ]);

    $do = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'delivered',
        'shipping_address' => 'addr',
        'recipient_name' => 'r',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $do->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 5,
        'quantity_delivered' => 5,
    ]);

    // Fully delivered but only partially invoiced → SO stays in SALES_ORDER.
    expect($s['salesOrder']->fresh()->status)->toBe('processing');
});

it('lock is one-way: cancelling an invoice after lock leaves SO in DONE (with drift)', function () {
    $s = makeObserverScenario(orderedQty: 2);
    $s['salesOrder']->update(['status' => 'processing']);

    $invoice = makeInvoiceFor($s, 'sent');
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'full',
        'quantity' => 2,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 200,
    ]);

    $do = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'delivered',
        'shipping_address' => 'addr',
        'recipient_name' => 'r',
    ]);
    DeliveryOrderItem::create([
        'delivery_order_id' => $do->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 2,
        'quantity_delivered' => 2,
    ]);

    // Fully invoiced + delivered but unpaid — not locked yet.
    expect($s['salesOrder']->fresh()->status)->toBe('processing');

    // Settle the invoice → SO locks to DONE.
    $invoice->update(['status' => 'paid']);
    expect($s['salesOrder']->fresh()->status)->toBe('delivered'); // locked

    // Cancel the invoice. The counter drops to 0 but the SO stays DONE.
    $invoice->update(['status' => 'cancelled']);

    expect($s['salesOrder']->fresh()->status)->toBe('delivered');
    expect((int) $s['soItem']->fresh()->quantity_invoiced)->toBe(0);
});

it('observer caps counters at SO item quantity even when invoice items overflow', function () {
    $s = makeObserverScenario(orderedQty: 5);
    $invoice = makeInvoiceFor($s);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'half',
        'quantity' => 6,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 600,
    ]);
    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'rest',
        'quantity' => 4,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 400,
    ]);

    expect((int) $s['soItem']->refresh()->quantity_invoiced)->toBe(5);
});

/**
 * The fulfillment observers must never lazy-load their way back to the
 * parent SO. The real write paths re-fetch existing rows and save them:
 * Delivery\Orders\Form does `$delivery->load('items')` then `save()`s
 * each item, editing an invoice line does the same. With lazy loading
 * disabled (PerformanceServiceProvider does this off-production), a
 * relation hop inside the observer throws "Attempted to lazy load [...]
 * but lazy loading is disabled".
 *
 * These tests set `$item->preventsLazyLoading = true` on the saved
 * instance explicitly. The static Model::preventLazyLoading() flag does
 * not propagate to model instances under the test harness, so without
 * this the guard is inert in tests and the regression can't be pinned.
 */
it('recomputes from a persisted DeliveryOrderItem without lazy-loading the parent SO', function () {
    $s = makeObserverScenario(orderedQty: 5);
    $s['salesOrder']->update(['status' => 'processing']);

    // The DO is already 'delivered' when the items are saved — that's
    // the real order of operations in Delivery\Orders\Form: persist the
    // status flip, then load('items') and save() each with its planned
    // quantity_delivered.
    $do = DeliveryOrder::create([
        'sales_order_id' => $s['salesOrder']->id,
        'warehouse_id' => $s['warehouse']->id,
        'user_id' => $s['user']->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'delivered',
        'shipping_address' => 'addr',
        'recipient_name' => 'r',
    ]);
    $created = DeliveryOrderItem::create([
        'delivery_order_id' => $do->id,
        'sales_order_item_id' => $s['soItem']->id,
        'product_id' => $s['product']->id,
        'quantity' => 5,
        'quantity_delivered' => 0,
    ]);

    // Re-fetch fresh (no relations loaded) and arm the lazy-load guard.
    $doItem = DeliveryOrderItem::findOrFail($created->id);
    $doItem->preventsLazyLoading = true;
    $doItem->quantity_delivered = 5;
    $doItem->save(); // must not throw a LazyLoadingViolationException

    expect((int) $s['soItem']->fresh()->quantity_delivered)->toBe(5);
});

it('recomputes from a persisted InvoiceItem without lazy-loading the parent SO', function () {
    $s = makeObserverScenario(orderedQty: 5);
    $invoice = makeInvoiceFor($s);
    $created = InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => $s['product']->id,
        'description' => 'line',
        'quantity' => 2,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 200,
    ]);

    $item = InvoiceItem::findOrFail($created->id);
    $item->preventsLazyLoading = true;
    $item->quantity = 4;
    $item->save(); // must not throw a LazyLoadingViolationException

    expect((int) $s['soItem']->fresh()->quantity_invoiced)->toBe(4);
});
