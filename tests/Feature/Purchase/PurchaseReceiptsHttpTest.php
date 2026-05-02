<?php

use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Purchase\PurchaseReceipt;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;

beforeEach(function () {
    $this->seed(ModulePermissionSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
    $this->actingAs($this->user);
});

it('renders the receipts index for an authorized user', function () {
    $this->get(route('purchase.receipts.index'))
        ->assertOk()
        ->assertSeeText('Goods Receipts');
});

it('builds a draft when /create is hit with a PO id', function () {
    $rfq = PurchaseRfq::factory()->purchaseOrder()->create();
    PurchaseRfqItem::factory()->create([
        'purchase_rfq_id' => $rfq->id,
        'product_id' => Product::factory(),
        'quantity' => 5,
    ]);
    Warehouse::factory()->create();

    $this->get(route('purchase.receipts.create', ['rfq' => $rfq->id]))
        ->assertRedirect();

    $receipt = PurchaseReceipt::where('purchase_rfq_id', $rfq->id)->first();

    expect($receipt)->not->toBeNull()
        ->and($receipt->items)->toHaveCount(1);
});

it('renders the edit form for an existing receipt', function () {
    $receipt = PurchaseReceipt::factory()->create([
        'warehouse_id' => Warehouse::factory(),
    ]);

    $this->get(route('purchase.receipts.edit', $receipt->id))
        ->assertOk()
        ->assertSeeText($receipt->reference);
});

it('redirects /create with no rfq back to PO index', function () {
    $this->get(route('purchase.receipts.create'))
        ->assertRedirect(route('purchase.orders.index'));
});

it('shows the Receive Goods button on a PO that is in purchase_order state', function () {
    $rfq = PurchaseRfq::factory()->purchaseOrder()->create();

    $this->get(route('purchase.orders.edit', $rfq->id))
        ->assertOk()
        ->assertSeeText('Receive Goods');
});

it('hides the Receive Goods button on a PO that is still an RFQ', function () {
    $rfq = PurchaseRfq::factory()->rfq()->create();

    $this->get(route('purchase.orders.edit', $rfq->id))
        ->assertOk()
        ->assertDontSeeText('Receive Goods');
});
