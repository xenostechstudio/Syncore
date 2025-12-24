<?php

use App\Enums\DeliveryOrderState;
use App\Enums\SalesOrderState;
use App\Livewire\Delivery\Orders\Form as DeliveryOrderForm;
use App\Livewire\Invoicing\Invoices\Form as InvoiceForm;
use App\Livewire\Sales\Orders\Form as SalesOrderForm;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Livewire\Livewire;

/**
 * Helper function to create a complete sales order scenario
 */
function makeSalesOrderScenario(int $quantity = 10, int $stockQty = 100): array
{
    $user = User::factory()->create();

    $warehouse = Warehouse::create([
        'name' => 'WH ' . uniqid(),
        'location' => 'Test',
        'contact_info' => null,
    ]);

    $product = Product::create([
        'name' => 'Product ' . uniqid(),
        'sku' => 'SKU-' . uniqid(),
        'barcode' => null,
        'product_type' => 'goods',
        'internal_reference' => null,
        'description' => null,
        'quantity' => $stockQty,
        'cost_price' => 100,
        'selling_price' => 150,
        'status' => 'in_stock',
        'warehouse_id' => $warehouse->id,
        'category_id' => null,
        'responsible_id' => null,
        'weight' => null,
        'volume' => null,
        'customer_lead_time' => 0,
        'receipt_note' => null,
        'delivery_note' => null,
        'internal_notes' => null,
        'is_favorite' => false,
        'sales_tax_id' => null,
    ]);

    InventoryStock::create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => $stockQty,
    ]);

    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Customer ' . uniqid(),
        'email' => 'test@example.com',
        'phone' => null,
        'address' => 'Test Address',
        'city' => null,
        'country' => 'Indonesia',
        'notes' => null,
        'salesperson_id' => null,
        'payment_term_id' => null,
        'payment_method' => null,
        'pricelist_id' => null,
        'banks' => null,
        'status' => 'active',
    ]);

    $salesOrder = SalesOrder::create([
        'order_number' => SalesOrder::generateOrderNumber(),
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'order_date' => now()->format('Y-m-d'),
        'expected_delivery_date' => now()->addDays(7)->format('Y-m-d'),
        'status' => SalesOrderState::SALES_ORDER->value,
        'subtotal' => $quantity * 150,
        'tax' => 0,
        'discount' => 0,
        'total' => $quantity * 150,
        'notes' => null,
        'terms' => null,
        'shipping_address' => 'Test Address',
    ]);

    $salesOrderItem = SalesOrderItem::create([
        'sales_order_id' => $salesOrder->id,
        'product_id' => $product->id,
        'tax_id' => null,
        'quantity' => $quantity,
        'quantity_invoiced' => 0,
        'quantity_delivered' => 0,
        'unit_price' => 150,
        'discount' => 0,
        'total' => $quantity * 150,
    ]);

    return [
        'user' => $user,
        'warehouse' => $warehouse,
        'product' => $product,
        'customer' => $customer,
        'salesOrder' => $salesOrder,
        'salesOrderItem' => $salesOrderItem,
    ];
}

/**
 * Helper to advance delivery order through status transitions
 */
function advanceDeliveryToStatus(DeliveryOrder $delivery, User $user, DeliveryOrderState $targetStatus): void
{
    $statusOrder = [
        DeliveryOrderState::PENDING,
        DeliveryOrderState::PICKED,
        DeliveryOrderState::IN_TRANSIT,
        DeliveryOrderState::DELIVERED,
    ];

    $currentIndex = array_search($delivery->status, $statusOrder);
    $targetIndex = array_search($targetStatus, $statusOrder);

    if ($currentIndex === false || $targetIndex === false || $targetIndex <= $currentIndex) {
        return;
    }

    for ($i = $currentIndex; $i < $targetIndex; $i++) {
        Livewire::actingAs($user)
            ->test(DeliveryOrderForm::class, ['id' => $delivery->id])
            ->call('openStatusTransitionModal')
            ->call('confirmStatusTransition');
        $delivery->refresh();
    }
}

// ============================================
// INVOICE QUANTITY TRACKING TESTS
// ============================================

it('tracks quantity_invoiced when creating invoice from sales order', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var SalesOrderItem $salesOrderItem */
    $salesOrderItem = $data['salesOrderItem'];

    // Verify initial state
    expect($salesOrderItem->quantity_invoiced)->toBe(0);
    expect($salesOrderItem->quantity_to_invoice)->toBe(10);

    // Create invoice via Livewire
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('invoiceType', 'regular')
        ->call('createInvoice');

    // Refresh and verify quantity_invoiced was updated
    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_invoiced)->toBe(10);
    expect($salesOrderItem->quantity_to_invoice)->toBe(0);

    // Verify invoice was created
    $invoice = Invoice::where('sales_order_id', $salesOrder->id)->first();
    expect($invoice)->not->toBeNull();
    expect($invoice->status)->toBe('draft');
});

it('decrements quantity_invoiced when invoice is cancelled', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var SalesOrderItem $salesOrderItem */
    $salesOrderItem = $data['salesOrderItem'];

    // Create invoice first
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('invoiceType', 'regular')
        ->call('createInvoice');

    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_invoiced)->toBe(10);

    // Get the invoice
    $invoice = Invoice::where('sales_order_id', $salesOrder->id)->first();

    // Cancel the invoice
    Livewire::actingAs($user)
        ->test(InvoiceForm::class, ['id' => $invoice->id])
        ->call('cancel');

    // Verify quantity_invoiced was decremented
    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_invoiced)->toBe(0);
    expect($salesOrderItem->quantity_to_invoice)->toBe(10);
});

it('allows creating new invoice after previous one is cancelled', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var SalesOrderItem $salesOrderItem */
    $salesOrderItem = $data['salesOrderItem'];

    // Create first invoice
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('invoiceType', 'regular')
        ->call('createInvoice');

    $firstInvoice = Invoice::where('sales_order_id', $salesOrder->id)->first();

    // Cancel first invoice
    Livewire::actingAs($user)
        ->test(InvoiceForm::class, ['id' => $firstInvoice->id])
        ->call('cancel');

    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_to_invoice)->toBe(10);

    // Create second invoice - should work now
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('invoiceType', 'regular')
        ->call('createInvoice');

    // Verify second invoice was created
    $invoices = Invoice::where('sales_order_id', $salesOrder->id)->get();
    expect($invoices->count())->toBe(2);

    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_invoiced)->toBe(10);
});

// ============================================
// DELIVERY ORDER FLOW TESTS
// ============================================

it('creates delivery order without updating quantity_delivered', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var SalesOrderItem $salesOrderItem */
    $salesOrderItem = $data['salesOrderItem'];
    
    /** @var Warehouse $warehouse */
    $warehouse = $data['warehouse'];

    // Verify initial state
    expect($salesOrderItem->quantity_delivered)->toBe(0);

    // Create delivery order via Livewire
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('deliveryWarehouseId', $warehouse->id)
        ->set('deliveryDate', now()->format('Y-m-d'))
        ->set('deliveryRecipientName', 'Test Recipient')
        ->call('createDeliveryOrder');

    // Verify quantity_delivered is still 0 (not updated on DO creation)
    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_delivered)->toBe(0);

    // Verify delivery order was created with pending status
    $delivery = DeliveryOrder::where('sales_order_id', $salesOrder->id)->first();
    expect($delivery)->not->toBeNull();
    expect($delivery->status)->toBe(DeliveryOrderState::PENDING);
});

it('updates quantity_delivered only when DO status becomes delivered', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var SalesOrderItem $salesOrderItem */
    $salesOrderItem = $data['salesOrderItem'];
    
    /** @var Warehouse $warehouse */
    $warehouse = $data['warehouse'];

    // Create delivery order
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('deliveryWarehouseId', $warehouse->id)
        ->set('deliveryDate', now()->format('Y-m-d'))
        ->set('deliveryRecipientName', 'Test Recipient')
        ->call('createDeliveryOrder');

    $delivery = DeliveryOrder::where('sales_order_id', $salesOrder->id)->first();

    // Advance to Picked - quantity_delivered should still be 0
    advanceDeliveryToStatus($delivery, $user, DeliveryOrderState::PICKED);
    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_delivered)->toBe(0);

    // Advance to In Transit - quantity_delivered should still be 0
    advanceDeliveryToStatus($delivery, $user, DeliveryOrderState::IN_TRANSIT);
    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_delivered)->toBe(0);

    // Advance to Delivered - NOW quantity_delivered should be updated
    advanceDeliveryToStatus($delivery, $user, DeliveryOrderState::DELIVERED);
    $salesOrderItem->refresh();
    expect($salesOrderItem->quantity_delivered)->toBe(10);
    expect($salesOrderItem->quantity_to_deliver)->toBe(0);
});

it('hides create delivery button when active DO exists', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var Warehouse $warehouse */
    $warehouse = $data['warehouse'];

    // Initially can create delivery
    expect($salesOrder->canCreateDeliveryOrder())->toBeTrue();

    // Create delivery order
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('deliveryWarehouseId', $warehouse->id)
        ->set('deliveryDate', now()->format('Y-m-d'))
        ->set('deliveryRecipientName', 'Test Recipient')
        ->call('createDeliveryOrder');

    // Now cannot create another delivery (active DO exists)
    $salesOrder->refresh();
    expect($salesOrder->canCreateDeliveryOrder())->toBeFalse();
    expect($salesOrder->hasActiveDeliveryOrder())->toBeTrue();
});

it('allows creating new delivery after previous one is cancelled', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var Warehouse $warehouse */
    $warehouse = $data['warehouse'];

    // Create first delivery order
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('deliveryWarehouseId', $warehouse->id)
        ->set('deliveryDate', now()->format('Y-m-d'))
        ->set('deliveryRecipientName', 'Test Recipient')
        ->call('createDeliveryOrder');

    $firstDelivery = DeliveryOrder::where('sales_order_id', $salesOrder->id)->first();

    // Cancel first delivery order
    Livewire::actingAs($user)
        ->test(DeliveryOrderForm::class, ['id' => $firstDelivery->id])
        ->call('cancel');

    // Now can create new delivery
    $salesOrder->refresh();
    expect($salesOrder->canCreateDeliveryOrder())->toBeTrue();

    // Create second delivery order
    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('deliveryWarehouseId', $warehouse->id)
        ->set('deliveryDate', now()->format('Y-m-d'))
        ->set('deliveryRecipientName', 'Test Recipient 2')
        ->call('createDeliveryOrder');

    // Verify two deliveries exist
    $deliveries = DeliveryOrder::where('sales_order_id', $salesOrder->id)->get();
    expect($deliveries->count())->toBe(2);
});

// ============================================
// LOCKING TESTS
// ============================================

it('locks sales order when invoice is created', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];

    expect($salesOrder->isLocked())->toBeFalse();

    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('invoiceType', 'regular')
        ->call('createInvoice');

    $salesOrder->refresh();
    expect($salesOrder->isLocked())->toBeTrue();
});

it('locks sales order when delivery order is created', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var Warehouse $warehouse */
    $warehouse = $data['warehouse'];

    expect($salesOrder->isLocked())->toBeFalse();

    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('deliveryWarehouseId', $warehouse->id)
        ->set('deliveryDate', now()->format('Y-m-d'))
        ->set('deliveryRecipientName', 'Test Recipient')
        ->call('createDeliveryOrder');

    $salesOrder->refresh();
    expect($salesOrder->isLocked())->toBeTrue();
});

it('unlocks sales order when all invoices are cancelled', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];

    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('invoiceType', 'regular')
        ->call('createInvoice');

    $salesOrder->refresh();
    expect($salesOrder->isLocked())->toBeTrue();

    $invoice = Invoice::where('sales_order_id', $salesOrder->id)->first();
    Livewire::actingAs($user)
        ->test(InvoiceForm::class, ['id' => $invoice->id])
        ->call('cancel');

    $salesOrder->refresh();
    expect($salesOrder->isLocked())->toBeFalse();
});

it('unlocks sales order when all delivery orders are cancelled', function () {
    $data = makeSalesOrderScenario(quantity: 10);
    
    /** @var User $user */
    $user = $data['user'];
    
    /** @var SalesOrder $salesOrder */
    $salesOrder = $data['salesOrder'];
    
    /** @var Warehouse $warehouse */
    $warehouse = $data['warehouse'];

    Livewire::actingAs($user)
        ->test(SalesOrderForm::class, ['id' => $salesOrder->id])
        ->set('deliveryWarehouseId', $warehouse->id)
        ->set('deliveryDate', now()->format('Y-m-d'))
        ->set('deliveryRecipientName', 'Test Recipient')
        ->call('createDeliveryOrder');

    $salesOrder->refresh();
    expect($salesOrder->isLocked())->toBeTrue();

    $delivery = DeliveryOrder::where('sales_order_id', $salesOrder->id)->first();
    Livewire::actingAs($user)
        ->test(DeliveryOrderForm::class, ['id' => $delivery->id])
        ->call('cancel');

    $salesOrder->refresh();
    expect($salesOrder->isLocked())->toBeFalse();
});
