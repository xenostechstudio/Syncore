<?php

/**
 * Regression: the header's related-documents strip (delivery + invoice
 * status badges) used to render only while the SO was in SALES_ORDER
 * state. Once the auto-lock flipped an order to DONE the badges
 * vanished — but a completed order is exactly when an operator most
 * wants to click through to the invoice/delivery it produced. The
 * header condition now covers SALES_ORDER *and* DONE.
 */

use App\Livewire\Sales\Orders\Form;
use App\Models\Delivery\DeliveryOrder;
use App\Models\Inventory\Warehouse;
use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('still renders delivery + invoice badges in the header when the SO is DONE', function () {
    $admin = actAsAdmin();

    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Customer '.uniqid(),
        'address' => 'addr',
        'country' => 'ID',
        'status' => 'active',
    ]);

    $warehouse = Warehouse::create([
        'name' => 'WH '.uniqid(),
        'location' => 'Test',
    ]);

    $order = SalesOrder::create([
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

    $invoice = Invoice::create([
        'invoice_number' => 'INV-DONEHDR-'.uniqid(),
        'customer_id' => $customer->id,
        'sales_order_id' => $order->id,
        'user_id' => $admin->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => 'paid',
        'subtotal' => 100,
        'tax' => 0,
        'discount' => 0,
        'total' => 100,
    ]);

    $delivery = DeliveryOrder::create([
        'sales_order_id' => $order->id,
        'warehouse_id' => $warehouse->id,
        'user_id' => $admin->id,
        'delivery_date' => now()->format('Y-m-d'),
        'status' => 'delivered',
        'shipping_address' => 'addr',
        'recipient_name' => 'someone',
    ]);

    // Flip the SO to DONE directly — the auto-lock path is covered
    // elsewhere; here we only care about the header rendering.
    DB::table('sales_orders')->where('id', $order->id)->update(['status' => 'delivered']);
    expect($order->refresh()->state)->toBe(\App\Enums\SalesOrderState::DONE);

    $html = Livewire::test(Form::class, ['id' => $order->id])->html();

    expect($html)->toContain($invoice->invoice_number);
    expect($html)->toContain($delivery->delivery_number);
});
