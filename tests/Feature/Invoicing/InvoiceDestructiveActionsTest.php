<?php

/**
 * Invoice form, Cancel-vs-Delete taxonomy (see "Destructive actions" in
 * CLAUDE.md). Delete = hard delete, only for a never-sent draft. Cancel
 * = state transition, for a sent invoice that became real. The two are
 * mutually exclusive by state, so the form surfaces exactly one.
 */

use App\Livewire\Invoicing\Invoices\Form;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Invoicing\Payment;
use App\Models\Sales\Customer;
use Livewire\Livewire;

function makeInvoice(string $status): Invoice
{
    $admin = auth()->user() ?? actAsAdmin();

    $customer = Customer::create([
        'type' => 'person',
        'name' => 'Customer '.uniqid(),
        'address' => 'addr',
        'country' => 'ID',
        'status' => 'active',
    ]);

    $invoice = Invoice::create([
        'invoice_number' => 'INV-'.uniqid(),
        'customer_id' => $customer->id,
        'sales_order_id' => null,
        'user_id' => $admin->id,
        'invoice_date' => now()->format('Y-m-d'),
        'due_date' => now()->addDays(30)->format('Y-m-d'),
        'status' => $status,
        'subtotal' => 100,
        'tax' => 0,
        'discount' => 0,
        'total' => 100,
    ]);

    InvoiceItem::create([
        'invoice_id' => $invoice->id,
        'product_id' => null,
        'description' => 'line',
        'quantity' => 1,
        'unit_price' => 100,
        'discount' => 0,
        'total' => 100,
    ]);

    return $invoice;
}

it('hard-deletes a never-sent draft invoice', function () {
    actAsAdmin();
    $invoice = makeInvoice('draft');

    Livewire::test(Form::class, ['id' => $invoice->id])
        ->call('delete')
        ->assertRedirect(route('invoicing.invoices.index'));

    expect(Invoice::withTrashed()->find($invoice->id))->toBeNull();
    expect(InvoiceItem::where('invoice_id', $invoice->id)->exists())->toBeFalse();
});

it('refuses to delete a sent invoice — directs to cancel instead', function () {
    actAsAdmin();
    $invoice = makeInvoice('sent');

    Livewire::test(Form::class, ['id' => $invoice->id])
        ->call('delete')
        ->assertNoRedirect();

    expect(Invoice::find($invoice->id))->not->toBeNull();
});

it('refuses to delete a draft invoice that has a recorded payment', function () {
    actAsAdmin();
    $invoice = makeInvoice('draft');

    Payment::create([
        'payment_number' => 'PAY-'.uniqid(),
        'invoice_id' => $invoice->id,
        'payment_date' => now()->format('Y-m-d'),
        'amount' => 50,
        'payment_method' => 'bank_transfer',
        'status' => 'completed',
    ]);

    Livewire::test(Form::class, ['id' => $invoice->id])
        ->call('delete')
        ->assertNoRedirect();

    expect(Invoice::find($invoice->id))->not->toBeNull();
});

it('offers Delete (not Cancel) for a draft invoice', function () {
    actAsAdmin();

    Livewire::test(Form::class, ['id' => makeInvoice('draft')->id])
        ->assertViewHas('canDeleteInvoice', true)
        ->assertViewHas('canCancelInvoice', false);
});

it('offers Cancel (not Delete) for sent / partial / overdue invoices', function () {
    actAsAdmin();

    foreach (['sent', 'partial', 'overdue'] as $status) {
        Livewire::test(Form::class, ['id' => makeInvoice($status)->id])
            ->assertViewHas('canCancelInvoice', true)
            ->assertViewHas('canDeleteInvoice', false);
    }
});

it('offers neither Cancel nor Delete for terminal invoices', function () {
    actAsAdmin();

    foreach (['paid', 'cancelled'] as $status) {
        Livewire::test(Form::class, ['id' => makeInvoice($status)->id])
            ->assertViewHas('canCancelInvoice', false)
            ->assertViewHas('canDeleteInvoice', false);
    }
});
