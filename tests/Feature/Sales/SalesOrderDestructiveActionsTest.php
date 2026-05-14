<?php

/**
 * The Sales Order form is the pilot for the Cancel-vs-Delete taxonomy
 * (see "Destructive actions" in CLAUDE.md). The old form had three
 * overlapping actions — Archive, Cancel Order, Delete. Now:
 *   - Delete = hard delete, only for a never-confirmed quotation.
 *   - Cancel = state transition, for an order that became real.
 *   - Archive is gone.
 * Cancel and Delete are mutually exclusive by state, so the header
 * dropdown only ever shows the one that applies.
 */

use App\Livewire\Sales\Orders\Form;
use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Livewire\Livewire;

function makeOrder(string $status): SalesOrder
{
    $admin = auth()->user() ?? actAsAdmin();

    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Customer '.uniqid(),
        'address' => 'addr',
        'country' => 'ID',
        'status' => 'active',
    ]);

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

    $order = SalesOrder::create([
        'customer_id' => $customer->id,
        'user_id' => $admin->id,
        'order_date' => now()->format('Y-m-d'),
        'status' => $status,
        'subtotal' => 100,
        'tax' => 0,
        'discount' => 0,
        'total' => 100,
        'shipping_address' => 'addr',
    ]);

    SalesOrderItem::create([
        'sales_order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 100,
    ]);

    return $order;
}

it('hard-deletes a never-confirmed quotation', function () {
    actAsAdmin();
    $order = makeOrder('draft'); // QUOTATION

    Livewire::test(Form::class, ['id' => $order->id])
        ->call('delete')
        ->assertRedirect(route('sales.orders.index'));

    // forceDelete — gone entirely, not just soft-deleted.
    expect(SalesOrder::withTrashed()->find($order->id))->toBeNull();
    expect(SalesOrderItem::where('sales_order_id', $order->id)->exists())->toBeFalse();
});

it('refuses to delete a confirmed sales order — directs to cancel instead', function () {
    actAsAdmin();
    $order = makeOrder('processing'); // SALES_ORDER

    Livewire::test(Form::class, ['id' => $order->id])
        ->call('delete')
        ->assertNoRedirect();

    // Refused — a confirmed order is Cancelled, never deleted.
    expect(SalesOrder::find($order->id))->not->toBeNull();
});

it('refuses to delete a quotation that already has an invoice (defensive guard)', function () {
    actAsAdmin();
    $order = makeOrder('draft');

    Invoice::create([
        'invoice_number' => 'INV-'.uniqid(),
        'customer_id' => $order->customer_id,
        'sales_order_id' => $order->id,
        'user_id' => $order->user_id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => 'draft',
        'subtotal' => 100,
        'tax' => 0,
        'discount' => 0,
        'total' => 100,
    ]);

    Livewire::test(Form::class, ['id' => $order->id])
        ->call('delete')
        ->assertNoRedirect();

    // Refused by the defensive child-existence guard.
    expect(SalesOrder::find($order->id))->not->toBeNull();
});

it('offers Delete (not Cancel) for a quotation', function () {
    actAsAdmin();
    $order = makeOrder('draft');

    Livewire::test(Form::class, ['id' => $order->id])
        ->assertViewHas('canDeleteOrder', true)
        ->assertViewHas('canCancelOrder', false);
});

it('offers Delete (not Cancel) for a sent quotation', function () {
    actAsAdmin();
    $order = makeOrder('confirmed'); // QUOTATION_SENT

    Livewire::test(Form::class, ['id' => $order->id])
        ->assertViewHas('canDeleteOrder', true)
        ->assertViewHas('canCancelOrder', false);
});

it('offers Cancel (not Delete) for a confirmed sales order', function () {
    actAsAdmin();
    $order = makeOrder('processing'); // SALES_ORDER

    Livewire::test(Form::class, ['id' => $order->id])
        ->assertViewHas('canCancelOrder', true)
        ->assertViewHas('canDeleteOrder', false);
});

it('offers neither Cancel nor Delete for a terminal order', function () {
    actAsAdmin();

    Livewire::test(Form::class, ['id' => makeOrder('cancelled')->id])
        ->assertViewHas('canCancelOrder', false)
        ->assertViewHas('canDeleteOrder', false);

    Livewire::test(Form::class, ['id' => makeOrder('delivered')->id])
        ->assertViewHas('canCancelOrder', false)
        ->assertViewHas('canDeleteOrder', false);
});

it('no longer exposes an archive action', function () {
    actAsAdmin();
    $order = makeOrder('draft');

    expect(method_exists(Form::class, 'archive'))->toBeFalse();

    $html = Livewire::test(Form::class, ['id' => $order->id])->html();
    expect($html)->not->toContain('wire:click="archive"');
});
