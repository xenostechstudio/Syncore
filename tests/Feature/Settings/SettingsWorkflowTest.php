<?php

/**
 * Phase 2D — pin the four "settings as workflow gates" wired in this commit:
 *
 *   1. SO auto_send_on_confirm  — listener gates the OrderConfirmation email
 *   2. SO stock_check_mode      — block / warn / allow on confirm
 *   3. PO auto_send_to_supplier — fires PurchaseOrderNotification on issue
 *   4. PO approval_threshold    — blocks confirm when total >= threshold AND
 *                                 user lacks purchase.approve permission
 *
 * Each setting is tested both in its on/blocking and off/passing states so
 * we catch regressions in either direction.
 */

use App\Listeners\Sales\SendSalesOrderConfirmationNotification;
use App\Livewire\Purchase\Rfq\Form as RfqForm;
use App\Livewire\Sales\Orders\Form as SalesOrderForm;
use App\Mail\OrderConfirmation;
use App\Mail\PurchaseOrderNotification;
use App\Models\Inventory\InventoryStock;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use App\Models\Purchase\Supplier;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Settings\PurchaseOrderSetting;
use App\Models\Settings\SalesOrderSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

describe('SO auto_send_on_confirm gates the OrderConfirmation listener', function () {
    it('does NOT send when the setting is off (default)', function () {
        Mail::fake();

        SalesOrderSetting::instance()->update(['auto_send_on_confirm' => false]);
        SalesOrderSetting::clearCache();

        $customer = Customer::factory()->create(['email' => 'customer@example.com']);
        $order = SalesOrder::factory()->create(['customer_id' => $customer->id, 'status' => 'processing']);

        (new SendSalesOrderConfirmationNotification())->handle(
            new \App\Events\SalesOrderConfirmed($order)
        );

        Mail::assertNothingSent();
    });

    it('sends the OrderConfirmation when the setting is on', function () {
        Mail::fake();

        SalesOrderSetting::instance()->update(['auto_send_on_confirm' => true]);
        SalesOrderSetting::clearCache();

        $customer = Customer::factory()->create(['email' => 'customer@example.com']);
        $order = SalesOrder::factory()->create(['customer_id' => $customer->id, 'status' => 'processing']);

        (new SendSalesOrderConfirmationNotification())->handle(
            new \App\Events\SalesOrderConfirmed($order)
        );

        Mail::assertSent(OrderConfirmation::class, fn ($m) => $m->hasTo('customer@example.com'));
    });

    it('skips silently when the customer has no email even with the setting on', function () {
        Mail::fake();

        SalesOrderSetting::instance()->update(['auto_send_on_confirm' => true]);
        SalesOrderSetting::clearCache();

        $customer = Customer::factory()->create(['email' => null]);
        $order = SalesOrder::factory()->create(['customer_id' => $customer->id, 'status' => 'processing']);

        (new SendSalesOrderConfirmationNotification())->handle(
            new \App\Events\SalesOrderConfirmed($order)
        );

        Mail::assertNothingSent();
    });
});

describe('SO stock_check_mode gates the confirm action', function () {
    function makeSalesOrderWithShortStock(): array
    {
        $customer  = Customer::factory()->create();
        $product   = Product::factory()->create(['name' => 'Widget']);
        $warehouse = Warehouse::factory()->create();

        // Have 5 in stock, but the order will need 10.
        InventoryStock::create([
            'warehouse_id' => $warehouse->id,
            'product_id'   => $product->id,
            'quantity'     => 5,
        ]);

        return compact('customer', 'product', 'warehouse');
    }

    it('blocks confirmation when mode=block and stock is insufficient', function () {
        ['customer' => $customer, 'product' => $product] = makeSalesOrderWithShortStock();

        SalesOrderSetting::instance()->update(['stock_check_mode' => 'block']);
        SalesOrderSetting::clearCache();

        actAsAdmin();

        $component = Livewire::test(SalesOrderForm::class)
            ->set('customer_id', $customer->id)
            ->set('items', [[
                'id'         => null,
                'product_id' => $product->id,
                'name'       => 'Widget',
                'sku'        => 'W-001',
                'tax_id'     => null,
                'quantity'   => 10,
                'unit_price' => 100_000,
                'discount'   => 0,
                'total'      => 1_000_000,
            ]])
            ->call('confirm');

        // Status should NOT have transitioned
        $component->assertSet('status', 'draft');
    });

    it('allows confirmation but warns when mode=warn and stock is insufficient', function () {
        ['customer' => $customer, 'product' => $product] = makeSalesOrderWithShortStock();

        SalesOrderSetting::instance()->update(['stock_check_mode' => 'warn']);
        SalesOrderSetting::clearCache();

        actAsAdmin();

        Livewire::test(SalesOrderForm::class)
            ->set('customer_id', $customer->id)
            ->set('items', [[
                'id'         => null,
                'product_id' => $product->id,
                'name'       => 'Widget',
                'sku'        => 'W-001',
                'tax_id'     => null,
                'quantity'   => 10,
                'unit_price' => 100_000,
                'discount'   => 0,
                'total'      => 1_000_000,
            ]])
            ->call('confirm')
            ->assertSet('status', \App\Enums\SalesOrderState::SALES_ORDER->value);
    });

    it('skips the check entirely when mode=allow', function () {
        ['customer' => $customer, 'product' => $product] = makeSalesOrderWithShortStock();

        SalesOrderSetting::instance()->update(['stock_check_mode' => 'allow']);
        SalesOrderSetting::clearCache();

        actAsAdmin();

        Livewire::test(SalesOrderForm::class)
            ->set('customer_id', $customer->id)
            ->set('items', [[
                'id'         => null,
                'product_id' => $product->id,
                'name'       => 'Widget',
                'sku'        => 'W-001',
                'tax_id'     => null,
                'quantity'   => 10,
                'unit_price' => 100_000,
                'discount'   => 0,
                'total'      => 1_000_000,
            ]])
            ->call('confirm')
            ->assertSet('status', \App\Enums\SalesOrderState::SALES_ORDER->value);
    });
});

describe('PO auto_send_to_supplier fires PurchaseOrderNotification', function () {
    it('sends to the supplier when the setting is on and supplier has email', function () {
        Mail::fake();
        PurchaseOrderSetting::instance()->update(['auto_send_to_supplier' => true]);
        PurchaseOrderSetting::clearCache();

        $supplier = Supplier::factory()->create(['email' => 'supplier@example.com']);
        $rfq = PurchaseRfq::create([
            'supplier_id' => $supplier->id,
            'order_date'  => now(),
            'status'      => 'sent',
            'subtotal'    => 100,
            'total'       => 100,
        ]);

        actAsAdmin();

        Livewire::test(RfqForm::class, ['id' => $rfq->id])
            ->call('confirmOrder');

        Mail::assertSent(
            PurchaseOrderNotification::class,
            fn ($m) => $m->hasTo('supplier@example.com')
        );
    });

    it('does NOT send when the setting is off', function () {
        Mail::fake();
        PurchaseOrderSetting::instance()->update(['auto_send_to_supplier' => false]);
        PurchaseOrderSetting::clearCache();

        $supplier = Supplier::factory()->create(['email' => 'supplier@example.com']);
        $rfq = PurchaseRfq::create([
            'supplier_id' => $supplier->id,
            'order_date'  => now(),
            'status'      => 'sent',
            'subtotal'    => 100,
            'total'       => 100,
        ]);

        actAsAdmin();

        Livewire::test(RfqForm::class, ['id' => $rfq->id])
            ->call('confirmOrder');

        Mail::assertNothingSent();
    });
});

describe('PO approval_threshold blocks confirmation', function () {
    /**
     * Helper: make an RFQ in 'sent' state with a single line that totals
     * the given amount, so RfqForm::mount + recalculateTotals end up with
     * $this->total = $amount. Without an item the form's recalculate
     * overwrites the persisted total to 0 — the approval check sees 0
     * and never trips.
     */
    function makeRfqWithTotal(int $amount, \App\Models\Purchase\Supplier $supplier): \App\Models\Purchase\PurchaseRfq
    {
        $rfq = \App\Models\Purchase\PurchaseRfq::create([
            'supplier_id' => $supplier->id,
            'order_date'  => now(),
            'status'      => 'sent',
            'subtotal'    => $amount,
            'total'       => $amount,
        ]);
        \App\Models\Purchase\PurchaseRfqItem::create([
            'purchase_rfq_id' => $rfq->id,
            'product_id'      => \App\Models\Inventory\Product::factory()->create()->id,
            'quantity'        => 1,
            'unit_price'      => $amount,
            'subtotal'        => $amount, // model column is `subtotal`, not `total`
        ]);

        return $rfq;
    }

    it('blocks confirmation when total >= threshold AND user lacks purchase.approve', function () {
        PurchaseOrderSetting::instance()->update(['approval_threshold' => 5_000_000]);
        PurchaseOrderSetting::clearCache();

        // User has confirm but NOT approve.
        $user = User::factory()->create();
        $user->givePermissionTo(['purchase.confirm', 'access.purchase']);
        $this->actingAs($user);

        $rfq = makeRfqWithTotal(10_000_000, Supplier::factory()->create());

        Livewire::test(RfqForm::class, ['id' => $rfq->id])
            ->call('confirmOrder');

        expect($rfq->fresh()->status)->toBe('sent');
    });

    it('allows confirmation when user has purchase.approve permission', function () {
        PurchaseOrderSetting::instance()->update(['approval_threshold' => 5_000_000]);
        PurchaseOrderSetting::clearCache();

        actAsAdmin(); // super-admin = * permissions, includes approve

        $rfq = makeRfqWithTotal(10_000_000, Supplier::factory()->create());

        Livewire::test(RfqForm::class, ['id' => $rfq->id])
            ->call('confirmOrder');

        expect($rfq->fresh()->status)->toBe('purchase_order');
    });

    it('allows confirmation when total is below the threshold (no approval needed)', function () {
        PurchaseOrderSetting::instance()->update(['approval_threshold' => 5_000_000]);
        PurchaseOrderSetting::clearCache();

        // Even without `approve`, small POs go through.
        $user = User::factory()->create();
        $user->givePermissionTo(['purchase.confirm', 'access.purchase']);
        $this->actingAs($user);

        $rfq = makeRfqWithTotal(1_000_000, Supplier::factory()->create());

        Livewire::test(RfqForm::class, ['id' => $rfq->id])
            ->call('confirmOrder');

        expect($rfq->fresh()->status)->toBe('purchase_order');
    });

    it('allows confirmation when no threshold is configured (null = disabled)', function () {
        PurchaseOrderSetting::instance()->update(['approval_threshold' => null]);
        PurchaseOrderSetting::clearCache();

        $user = User::factory()->create();
        $user->givePermissionTo(['purchase.confirm', 'access.purchase']);
        $this->actingAs($user);

        $rfq = makeRfqWithTotal(999_999_999, Supplier::factory()->create());

        Livewire::test(RfqForm::class, ['id' => $rfq->id])
            ->call('confirmOrder');

        expect($rfq->fresh()->status)->toBe('purchase_order');
    });
});
