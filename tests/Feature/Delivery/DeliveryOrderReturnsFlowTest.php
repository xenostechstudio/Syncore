<?php

use App\Enums\DeliveryOrderState;
use App\Livewire\Delivery\Orders\Form as DeliveryOrderForm;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Delivery\DeliveryOrderItem;
use App\Models\Delivery\DeliveryReturn;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Livewire\Livewire;

function makeDeliveryOrderScenario(int $stockQty = 10, int $toDeliver = 5): array
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

    InventoryStock::create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => $stockQty,
    ]);

    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Customer ' . uniqid(),
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

    $salesOrder = SalesOrder::create([
        'order_number' => SalesOrder::generateOrderNumber(),
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

    $salesOrderItem = SalesOrderItem::create([
        'sales_order_id' => $salesOrder->id,
        'product_id' => $product->id,
        'tax_id' => null,
        'quantity' => $toDeliver,
        'unit_price' => 100,
        'discount' => 0,
        'total' => $toDeliver * 100,
    ]);

    $deliveryOrder = DeliveryOrder::create([
        'delivery_number' => DeliveryOrder::generateDeliveryNumber(),
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

    $deliveryOrderItem = DeliveryOrderItem::create([
        'delivery_order_id' => $deliveryOrder->id,
        'sales_order_item_id' => $salesOrderItem->id,
        'quantity_to_deliver' => $toDeliver,
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

function advanceDeliveryOrderStatus(DeliveryOrder $deliveryOrder, User $user, int $steps = 1): void
{
    for ($i = 0; $i < $steps; $i++) {
        Livewire::actingAs($user)
            ->test(DeliveryOrderForm::class, ['id' => $deliveryOrder->id])
            ->call('openStatusTransitionModal')
            ->call('confirmStatusTransition');

        $deliveryOrder->refresh();
    }
}

it('advances through pending -> picked -> in_transit -> delivered and posts WH/OUT once', function () {
    $data = makeDeliveryOrderScenario(stockQty: 10, toDeliver: 5);

    /** @var User $user */
    $user = $data['user'];

    /** @var DeliveryOrder $delivery */
    $delivery = $data['deliveryOrder'];

    expect($delivery->refresh()->status)->toBe(DeliveryOrderState::PENDING);

    advanceDeliveryOrderStatus($delivery, $user, 1);
    expect($delivery->refresh()->status)->toBe(DeliveryOrderState::PICKED);

    advanceDeliveryOrderStatus($delivery, $user, 1);
    expect($delivery->refresh()->status)->toBe(DeliveryOrderState::IN_TRANSIT);

    advanceDeliveryOrderStatus($delivery, $user, 1);
    expect($delivery->refresh()->status)->toBe(DeliveryOrderState::DELIVERED);

    $stock = InventoryStock::query()
        ->where('warehouse_id', $delivery->warehouse_id)
        ->where('product_id', $data['product']->id)
        ->firstOrFail();

    expect((int) $stock->quantity)->toBe(5);

    $deliveryItem = DeliveryOrderItem::query()->where('delivery_order_id', $delivery->id)->firstOrFail();
    expect((int) $deliveryItem->quantity_delivered)->toBe(5);

    $adjustment = InventoryAdjustment::query()
        ->where('source_delivery_order_id', $delivery->id)
        ->first();

    expect($adjustment)->not->toBeNull();
    expect($adjustment->posted_at)->not->toBeNull();

    expect(InventoryAdjustment::query()->where('source_delivery_order_id', $delivery->id)->count())->toBe(1);
});

it('blocks delivering when warehouse stock is insufficient', function () {
    $data = makeDeliveryOrderScenario(stockQty: 1, toDeliver: 5);

    /** @var User $user */
    $user = $data['user'];

    /** @var DeliveryOrder $delivery */
    $delivery = $data['deliveryOrder'];

    expect($delivery->refresh()->status)->toBe(DeliveryOrderState::PENDING);

    Livewire::actingAs($user)
        ->test(DeliveryOrderForm::class, ['id' => $delivery->id])
        ->call('openStatusTransitionModal')
        ->assertSet('status_modal_can_confirm', false)
        ->call('confirmStatusTransition');

    expect($delivery->refresh()->status)->toBe(DeliveryOrderState::PENDING);

    expect(InventoryAdjustment::query()->where('source_delivery_order_id', $delivery->id)->count())
        ->toBe(0);
});

it('creates a return and receiving it is idempotent (WH/IN posted once)', function () {
    $data = makeDeliveryOrderScenario(stockQty: 10, toDeliver: 5);

    /** @var User $user */
    $user = $data['user'];

    /** @var DeliveryOrder $delivery */
    $delivery = $data['deliveryOrder'];

    /** @var Product $product */
    $product = $data['product'];

    /** @var Warehouse $warehouse */
    $warehouse = $data['warehouse'];

    advanceDeliveryOrderStatus($delivery, $user, 3);
    expect($delivery->refresh()->status)->toBe(DeliveryOrderState::DELIVERED);

    $before = (int) InventoryStock::query()
        ->where('warehouse_id', $warehouse->id)
        ->where('product_id', $product->id)
        ->value('quantity');

    $component = Livewire::actingAs($user)
        ->test(DeliveryOrderForm::class, ['id' => $delivery->id])
        ->call('openReturnModal');

    $returnItems = $component->get('return_items');
    $returnItems[0]['quantity'] = 2;

    $component
        ->set('return_warehouse_id', $warehouse->id)
        ->set('return_items', $returnItems)
        ->call('createReturn');

    $ret = DeliveryReturn::query()
        ->where('delivery_order_id', $delivery->id)
        ->latest('id')
        ->firstOrFail();

    expect($ret->status)->toBe('draft');

    Livewire::actingAs($user)
        ->test(DeliveryOrderForm::class, ['id' => $delivery->id])
        ->call('receiveReturn', $ret->id);

    $afterFirst = (int) InventoryStock::query()
        ->where('warehouse_id', $warehouse->id)
        ->where('product_id', $product->id)
        ->value('quantity');

    expect($afterFirst)->toBe($before + 2);
    expect($ret->refresh()->status)->toBe('received');

    expect(InventoryAdjustment::query()->where('source_delivery_return_id', $ret->id)->count())
        ->toBe(1);

    Livewire::actingAs($user)
        ->test(DeliveryOrderForm::class, ['id' => $delivery->id])
        ->call('receiveReturn', $ret->id);

    $afterSecond = (int) InventoryStock::query()
        ->where('warehouse_id', $warehouse->id)
        ->where('product_id', $product->id)
        ->value('quantity');

    expect($afterSecond)->toBe($afterFirst);
    expect(InventoryAdjustment::query()->where('source_delivery_return_id', $ret->id)->count())
        ->toBe(1);
});
