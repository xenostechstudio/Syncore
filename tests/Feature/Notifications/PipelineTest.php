<?php

use App\Events\InvoicePaid;
use App\Events\InvoiceOverdue;
use App\Events\OpportunityLost;
use App\Events\OpportunityWon;
use App\Events\PayrollProcessed;
use App\Events\VendorBillPaid;
use App\Models\CRM\Opportunity;
use App\Models\HR\PayrollPeriod;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use App\Models\Purchase\VendorBill;
use App\Models\Purchase\VendorBillPayment;
use App\Models\Sales\Customer;
use App\Models\SystemNotification;

/**
 * End-to-end smoke for the notification pipeline.
 *
 *   Event::dispatch  -> ShouldQueue listener (sync in tests) ->
 *   NotificationService::create -> row in system_notifications
 *
 * If a listener silently broke (rename, deleted, throws), the row
 * never lands. Each test dispatches one event and asserts the
 * matching SystemNotification row exists. The Form/Livewire layer is
 * out of scope here — see EventServiceProvider for the wiring.
 */

beforeEach(function () {
    actAsAdmin();
});

it('writes a SystemNotification when OpportunityWon fires', function () {
    $opp = Opportunity::factory()->create(['assigned_to' => auth()->id()]);

    OpportunityWon::dispatch($opp);

    expect(SystemNotification::where('type', 'opportunity_won')
        ->where('user_id', auth()->id())
        ->exists())->toBeTrue();
});

it('writes a SystemNotification when OpportunityLost fires', function () {
    $opp = Opportunity::factory()->create(['assigned_to' => auth()->id()]);

    OpportunityLost::dispatch($opp, 'Lost to competitor');

    expect(SystemNotification::where('type', 'opportunity_lost')
        ->where('user_id', auth()->id())
        ->exists())->toBeTrue();
});

it('writes a SystemNotification when InvoicePaid fires', function () {
    $customer = Customer::factory()->create();
    $invoice = Invoice::create([
        'invoice_number' => 'INV-PIPE-1',
        'customer_id'    => $customer->id,
        'invoice_date'   => now(),
        'due_date'       => now()->addDays(30),
        'status'         => 'paid',
        'subtotal'       => 1000, 'tax' => 110, 'total' => 1110,
        'paid_amount'    => 1110,
    ]);
    $payment = Payment::create([
        'payment_number' => 'PAY-PIPE-1',
        'invoice_id'     => $invoice->id,
        'payment_date'   => now(),
        'amount'         => 1110,
        'payment_method' => 'Bank Transfer',
        'status'         => 'completed',
    ]);

    InvoicePaid::dispatch($invoice, $payment);

    expect(SystemNotification::where('type', 'payment_received')->exists())
        ->toBeTrue();
});

it('writes a SystemNotification when InvoiceOverdue fires', function () {
    $customer = Customer::factory()->create();
    $invoice = Invoice::create([
        'invoice_number' => 'INV-PIPE-2',
        'customer_id'    => $customer->id,
        'invoice_date'   => now()->subDays(60),
        'due_date'       => now()->subDays(30),
        'status'         => 'overdue',
        'subtotal'       => 1000, 'tax' => 110, 'total' => 1110,
    ]);

    InvoiceOverdue::dispatch($invoice);

    expect(SystemNotification::where('type', 'invoice_overdue')->exists())
        ->toBeTrue();
});

it('writes a SystemNotification when PayrollProcessed fires', function () {
    $period = PayrollPeriod::factory()->create(['status' => 'paid']);

    PayrollProcessed::dispatch($period);

    expect(SystemNotification::where('type', 'payroll_processed')->exists())
        ->toBeTrue();
});

it('writes a SystemNotification when VendorBillPaid fires', function () {
    $bill = VendorBill::factory()->create([
        'total'       => 500,
        'paid_amount' => 500,
        'status'      => 'paid',
    ]);
    $payment = VendorBillPayment::factory()->create([
        'vendor_bill_id' => $bill->id,
        'amount'         => 500,
    ]);

    VendorBillPaid::dispatch($bill, $payment);

    expect(SystemNotification::where('type', 'vendor_bill_paid')->exists())
        ->toBeTrue();
});
