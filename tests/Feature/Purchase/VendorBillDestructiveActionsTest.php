<?php

/**
 * Vendor Bill form, Cancel-vs-Delete taxonomy (see "Destructive
 * actions" in CLAUDE.md). Delete = hard delete, only for a
 * never-confirmed draft. Cancel = state transition, for a confirmed
 * (pending) bill. Mutually exclusive by state.
 */

use App\Livewire\Purchase\Bills\Form;
use App\Models\Purchase\Supplier;
use App\Models\Purchase\VendorBill;
use App\Models\Purchase\VendorBillItem;
use App\Models\Purchase\VendorBillPayment;
use Livewire\Livewire;

function makeBill(string $status): VendorBill
{
    $supplier = Supplier::factory()->create();

    $bill = VendorBill::create([
        'bill_number' => 'VB-'.uniqid(),
        'supplier_id' => $supplier->id,
        'bill_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => $status,
        'subtotal' => 100,
        'tax' => 0,
        'total' => 100,
        'paid_amount' => 0,
    ]);

    VendorBillItem::create([
        'vendor_bill_id' => $bill->id,
        'product_id' => null,
        'description' => 'line',
        'quantity' => 1,
        'unit_price' => 100,
        'tax_amount' => 0,
        'total' => 100,
    ]);

    return $bill;
}

it('hard-deletes a never-confirmed draft bill', function () {
    actAsAdmin();
    $bill = makeBill('draft');

    Livewire::test(Form::class, ['id' => $bill->id])
        ->call('delete')
        ->assertRedirect(route('purchase.bills.index'));

    expect(VendorBill::withTrashed()->find($bill->id))->toBeNull();
    expect(VendorBillItem::where('vendor_bill_id', $bill->id)->exists())->toBeFalse();
});

it('refuses to delete a confirmed bill — directs to cancel instead', function () {
    actAsAdmin();
    $bill = makeBill('pending');

    Livewire::test(Form::class, ['id' => $bill->id])
        ->call('delete')
        ->assertNoRedirect();

    expect(VendorBill::find($bill->id))->not->toBeNull();
});

it('refuses to delete a draft bill that has a recorded payment', function () {
    actAsAdmin();
    $bill = makeBill('draft');

    VendorBillPayment::create([
        'vendor_bill_id' => $bill->id,
        'payment_date' => now()->format('Y-m-d'),
        'amount' => 50,
        'payment_method' => 'bank_transfer',
    ]);

    Livewire::test(Form::class, ['id' => $bill->id])
        ->call('delete')
        ->assertNoRedirect();

    expect(VendorBill::find($bill->id))->not->toBeNull();
});

it('offers Delete (not Cancel) for a draft bill', function () {
    actAsAdmin();

    Livewire::test(Form::class, ['id' => makeBill('draft')->id])
        ->assertViewHas('canDeleteBill', true)
        ->assertViewHas('canCancelBill', false);
});

it('offers Cancel (not Delete) for a pending bill', function () {
    actAsAdmin();

    Livewire::test(Form::class, ['id' => makeBill('pending')->id])
        ->assertViewHas('canCancelBill', true)
        ->assertViewHas('canDeleteBill', false);
});

it('offers neither Cancel nor Delete for partial / overdue / terminal bills', function () {
    actAsAdmin();

    foreach (['partial', 'overdue', 'paid', 'cancelled'] as $status) {
        Livewire::test(Form::class, ['id' => makeBill($status)->id])
            ->assertViewHas('canCancelBill', false)
            ->assertViewHas('canDeleteBill', false);
    }
});
