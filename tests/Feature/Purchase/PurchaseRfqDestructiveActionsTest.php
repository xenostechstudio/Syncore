<?php

/**
 * Purchase RFQ / Order form, Cancel-vs-Delete taxonomy (see
 * "Destructive actions" in CLAUDE.md). One model (PurchaseRfq) with two
 * form views (Rfq + Orders, which extends it). Delete = hard delete,
 * only for an RFQ never confirmed into a Purchase Order. Cancel = state
 * transition, for a confirmed PO. Mutually exclusive by state.
 */

use App\Livewire\Purchase\Rfq\Form;
use App\Models\Inventory\Product;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use App\Models\Purchase\Supplier;
use Livewire\Livewire;

function makeRfqForDestructiveTest(string $status): PurchaseRfq
{
    $supplier = Supplier::factory()->create();

    $rfq = PurchaseRfq::create([
        'supplier_id' => $supplier->id,
        'order_date' => now(),
        'status' => $status,
        'subtotal' => 100,
        'total' => 100,
    ]);

    PurchaseRfqItem::create([
        'purchase_rfq_id' => $rfq->id,
        'product_id' => Product::factory()->create()->id,
        'description' => 'line',
        'quantity' => 1,
        'unit_price' => 100,
        'subtotal' => 100,
    ]);

    return $rfq;
}

it('hard-deletes an RFQ that was never confirmed into a Purchase Order', function () {
    actAsAdmin();
    $rfq = makeRfqForDestructiveTest('rfq');

    Livewire::test(Form::class, ['id' => $rfq->id])
        ->call('delete')
        ->assertRedirect(route('purchase.rfq.index'));

    expect(PurchaseRfq::withTrashed()->find($rfq->id))->toBeNull();
    expect(PurchaseRfqItem::where('purchase_rfq_id', $rfq->id)->exists())->toBeFalse();
});

it('refuses to delete a confirmed Purchase Order — directs to cancel instead', function () {
    actAsAdmin();
    $rfq = makeRfqForDestructiveTest('purchase_order');

    Livewire::test(Form::class, ['id' => $rfq->id])
        ->call('delete')
        ->assertNoRedirect();

    expect(PurchaseRfq::find($rfq->id))->not->toBeNull();
});

it('offers Delete (not Cancel) for an RFQ / sent RFQ', function () {
    actAsAdmin();

    foreach (['rfq', 'sent'] as $status) {
        Livewire::test(Form::class, ['id' => makeRfqForDestructiveTest($status)->id])
            ->assertViewHas('canDeleteRfq', true)
            ->assertViewHas('canCancelRfq', false);
    }
});

it('offers Cancel (not Delete) for a confirmed Purchase Order', function () {
    actAsAdmin();

    Livewire::test(Form::class, ['id' => makeRfqForDestructiveTest('purchase_order')->id])
        ->assertViewHas('canCancelRfq', true)
        ->assertViewHas('canDeleteRfq', false);
});

it('offers neither Cancel nor Delete once a PO is received / billed / cancelled', function () {
    actAsAdmin();

    foreach (['partially_received', 'received', 'billed', 'cancelled'] as $status) {
        Livewire::test(Form::class, ['id' => makeRfqForDestructiveTest($status)->id])
            ->assertViewHas('canCancelRfq', false)
            ->assertViewHas('canDeleteRfq', false);
    }
});
