<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Seed module permissions + roles before every Feature test. RefreshDatabase
 * wipes between tests, so re-seeding each time is required for any test
 * that exercises a Livewire action protected by WithPermissions::authorizePermission().
 *
 * Adds ~10ms per test; the tradeoff is that authorizePermission() works
 * uniformly without each test having to remember to seed.
 */
uses()
    ->beforeEach(function () {
        $this->seed(\Database\Seeders\ModulePermissionSeeder::class);

        // Per-process settings caches survive RefreshDatabase (static
        // properties live in the PHP process, not the DB). Clear them so
        // each test sees a clean settings state.
        \App\Models\Settings\SalesOrderSetting::clearCache();
        \App\Models\Settings\PurchaseOrderSetting::clearCache();
    })
    ->in('Feature');

/**
 * Create a super-admin user and act as them. Use in tests that exercise
 * privileged actions but don't care about per-permission gating.
 */
function actAsAdmin(): \App\Models\User
{
    $user = \App\Models\User::factory()->create();
    $user->assignRole('super-admin');
    test()->actingAs($user);

    return $user;
}

/**
 * Spin up a complete Sales Order → Delivery Order scenario suitable for
 * exercising the delivery-side state machine, POD, feedback, and returns
 * flows. Returns the constructed graph keyed by role so tests can pull
 * what they need.
 */
function makeDeliveryOrderScenario(int $stockQty = 10, int $toDeliver = 5): array
{
    $user = \App\Models\User::factory()->create();

    $warehouse = \App\Models\Inventory\Warehouse::create([
        'name' => 'WH '.uniqid(),
        'location' => 'Test',
        'contact_info' => null,
    ]);

    $product = \App\Models\Inventory\Product::create([
        'name' => 'Product '.uniqid(),
        'sku' => 'SKU-'.uniqid(),
        'barcode' => null,
        'product_type' => 'goods',
        'internal_reference' => null,
        'description' => null,
        'quantity' => $stockQty,
        'cost_price' => 10,
        'selling_price' => 15,
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

    \App\Models\Inventory\InventoryStock::create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => $stockQty,
    ]);

    $customer = \App\Models\Sales\Customer::create([
        'type' => 'person',
        'name' => 'Customer '.uniqid(),
        'email' => null,
        'phone' => null,
        'address' => 'Shipping Address',
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

    $salesOrder = \App\Models\Sales\SalesOrder::create([
        'customer_id' => $customer->id,
        'user_id' => $user->id,
        'order_date' => now()->format('Y-m-d'),
        'expected_delivery_date' => null,
        'status' => 'draft',
        'subtotal' => 0,
        'tax' => 0,
        'discount' => 0,
        'total' => 0,
        'notes' => null,
        'terms' => null,
        'shipping_address' => 'Shipping Address',
    ]);

    $salesOrderItem = \App\Models\Sales\SalesOrderItem::create([
        'sales_order_id' => $salesOrder->id,
        'product_id' => $product->id,
        'tax_id' => null,
        'quantity' => $toDeliver,
        'unit_price' => 100,
        'discount' => 0,
        'total' => $toDeliver * 100,
    ]);

    $deliveryOrder = \App\Models\Delivery\DeliveryOrder::create([
        'sales_order_id' => $salesOrder->id,
        'warehouse_id' => $warehouse->id,
        'user_id' => $user->id,
        'delivery_date' => now()->format('Y-m-d'),
        'actual_delivery_date' => null,
        'status' => 'pending',
        'shipping_address' => 'Shipping Address',
        'recipient_name' => 'Receiver',
        'recipient_phone' => null,
        'notes' => null,
        'tracking_number' => null,
        'courier' => null,
    ]);

    $deliveryOrderItem = \App\Models\Delivery\DeliveryOrderItem::create([
        'delivery_order_id' => $deliveryOrder->id,
        'sales_order_item_id' => $salesOrderItem->id,
        'quantity' => $toDeliver,
        'quantity_delivered' => 0,
    ]);

    return [
        'user' => $user,
        'warehouse' => $warehouse,
        'product' => $product,
        'customer' => $customer,
        'salesOrder' => $salesOrder,
        'salesOrderItem' => $salesOrderItem,
        'deliveryOrder' => $deliveryOrder,
        'deliveryOrderItem' => $deliveryOrderItem,
    ];
}

/**
 * Walk a DeliveryOrder forward through the state machine by invoking the
 * same Livewire action a human would click. One step = one transition.
 */
function advanceDeliveryOrderStatus(
    \App\Models\Delivery\DeliveryOrder $deliveryOrder,
    \App\Models\User $user,
    int $steps = 1
): void {
    for ($i = 0; $i < $steps; $i++) {
        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\Delivery\Orders\Form::class, ['id' => $deliveryOrder->id])
            ->call('openStatusTransitionModal')
            ->call('confirmStatusTransition');

        $deliveryOrder->refresh();
    }
}
