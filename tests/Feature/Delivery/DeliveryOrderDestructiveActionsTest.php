<?php

/**
 * Delivery Order form, Cancel-vs-Delete taxonomy (see "Destructive
 * actions" in CLAUDE.md). Delete = hard delete, only while still
 * PENDING (nothing shipped, no stock moved). Cancel = state transition,
 * for a picked / in-transit delivery that became real. The two are
 * mutually exclusive by state.
 */

use App\Livewire\Delivery\Orders\Form;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Livewire\Livewire;

function makeDelivery(string $status): DeliveryOrder
{
    $admin = auth()->user() ?? actAsAdmin();

    $warehouse = Warehouse::create(['name' => 'WH '.uniqid(), 'location' => 'Test']);

    $product = Product::create([
        'name' => 'Product '.uniqid(),
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
        'user_id' => $admin->id,
        'order_date' => now()->format('Y-m-d'),
        'status' => 'processing',
        'subtotal' => 100,
        'tax' => 0,
        'discount' => 0,
        'total' => 100,
        'shipping_address' => 'addr',
    ]);

    $soItem = SalesOrderItem::create([
        'sales_order_id' => $salesOrder->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 100,
    ]);

    $delivery = DeliveryOrder::create([
        'sales_order_id' => $salesOrder->id,
        'warehouse_id' => $warehouse->id,
        'user_id' => $admin->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => $status,
        'shipping_address' => 'addr',
        'recipient_name' => 'someone',
    ]);

    DeliveryOrderItem::create([
        'delivery_order_id' => $delivery->id,
        'sales_order_item_id' => $soItem->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'quantity_delivered' => 0,
    ]);

    return $delivery;
}

it('hard-deletes a still-pending delivery order', function () {
    actAsAdmin();
    $delivery = makeDelivery('pending');

    Livewire::test(Form::class, ['id' => $delivery->id])
        ->call('delete')
        ->assertRedirect(route('delivery.orders.index'));

    expect(DeliveryOrder::withTrashed()->find($delivery->id))->toBeNull();
    expect(DeliveryOrderItem::where('delivery_order_id', $delivery->id)->exists())->toBeFalse();
});

it('refuses to delete a picked or shipped delivery — directs to cancel instead', function () {
    actAsAdmin();

    foreach (['picked', 'in_transit', 'delivered'] as $status) {
        $delivery = makeDelivery($status);

        Livewire::test(Form::class, ['id' => $delivery->id])
            ->call('delete')
            ->assertNoRedirect();

        expect(DeliveryOrder::find($delivery->id))->not->toBeNull();
    }
});

it('offers Delete (not Cancel) for a pending delivery', function () {
    actAsAdmin();

    Livewire::test(Form::class, ['id' => makeDelivery('pending')->id])
        ->assertViewHas('canDeleteDelivery', true)
        ->assertViewHas('canCancelDelivery', false);
});

it('offers Cancel (not Delete) for picked / in-transit deliveries', function () {
    actAsAdmin();

    foreach (['picked', 'in_transit'] as $status) {
        Livewire::test(Form::class, ['id' => makeDelivery($status)->id])
            ->assertViewHas('canCancelDelivery', true)
            ->assertViewHas('canDeleteDelivery', false);
    }
});

it('offers neither Cancel nor Delete for terminal deliveries', function () {
    actAsAdmin();

    foreach (['delivered', 'failed', 'returned', 'cancelled'] as $status) {
        Livewire::test(Form::class, ['id' => makeDelivery($status)->id])
            ->assertViewHas('canCancelDelivery', false)
            ->assertViewHas('canDeleteDelivery', false);
    }
});
