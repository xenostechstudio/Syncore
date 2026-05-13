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

// Shared scenario + advancement helpers live in tests/Pest.php so the
// action-button gating tests can reuse them.

it('advances through pending -> picked -> in_transit -> delivered and posts WH/OUT once', function () {
    $data = makeDeliveryOrderScenario(stockQty: 10, toDeliver: 5);

    /** @var User $user */
    $user = $data['user'];

    /** @var DeliveryOrder $delivery */
    $delivery = $data['deliveryOrder'];

    expect($delivery->refresh()->state)->toBe(DeliveryOrderState::PENDING);

    advanceDeliveryOrderStatus($delivery, $user, 1);
    expect($delivery->refresh()->state)->toBe(DeliveryOrderState::PICKED);

    advanceDeliveryOrderStatus($delivery, $user, 1);
    expect($delivery->refresh()->state)->toBe(DeliveryOrderState::IN_TRANSIT);

    advanceDeliveryOrderStatus($delivery, $user, 1);
    expect($delivery->refresh()->state)->toBe(DeliveryOrderState::DELIVERED);

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

    expect($delivery->refresh()->state)->toBe(DeliveryOrderState::PENDING);

    Livewire::actingAs($user)
        ->test(DeliveryOrderForm::class, ['id' => $delivery->id])
        ->call('openStatusTransitionModal')
        ->assertSet('status_modal_can_confirm', false)
        ->call('confirmStatusTransition');

    expect($delivery->refresh()->state)->toBe(DeliveryOrderState::PENDING);

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
    expect($delivery->refresh()->state)->toBe(DeliveryOrderState::DELIVERED);

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
