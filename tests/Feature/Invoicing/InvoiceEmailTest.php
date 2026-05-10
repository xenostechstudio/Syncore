<?php

/**
 * Coverage for sendInvoiceEmail() — the action customers' invoices flow
 * through every time the seller hits "Send by Email" from the share modal.
 * Until this test existed, the path was completely uncovered. The shape
 * matches every other email/PDF flow we've found shipped bugs in this
 * session.
 */

use App\Livewire\Invoicing\Invoices\Form;
use App\Mail\InvoiceNotification;
use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->customer = Customer::factory()->create([
        'email' => 'customer@example.com',
        'name'  => 'Acme Co',
    ]);
    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'subtotal'    => 750_000,
        'total'       => 750_000,
        'status'      => 'draft',
    ]);

    $this->user = User::factory()->create()->assignRole('super-admin');
    $this->actingAs($this->user);

    Mail::fake();
});

it('sends an InvoiceNotification mailable to the customer email', function () {
    Livewire::test(Form::class, ['id' => $this->invoice->id])
        ->call('openShareModal')
        ->set('emailTo', 'customer@example.com')
        ->set('emailSubject', 'Your invoice')
        ->set('emailMessage', 'Hello, please pay.')
        ->call('sendInvoiceEmail');

    Mail::assertSent(InvoiceNotification::class, function ($mail) {
        return $mail->hasTo('customer@example.com')
            && $mail->invoice->id === $this->invoice->id;
    });
});

it('transitions the invoice from draft to sent after a successful send', function () {
    Livewire::test(Form::class, ['id' => $this->invoice->id])
        ->call('openShareModal')
        ->set('emailTo', 'customer@example.com')
        ->set('emailSubject', 'Your invoice')
        ->set('emailMessage', 'Hello.')
        ->call('sendInvoiceEmail');

    expect($this->invoice->fresh()->status)->toBe('sent');
});

it('rejects without invoicing.send permission', function () {
    $unprivileged = User::factory()->create();
    $this->actingAs($unprivileged);

    Livewire::test(Form::class, ['id' => $this->invoice->id])
        ->call('openShareModal')
        ->set('emailTo', 'customer@example.com')
        ->set('emailSubject', 'Your invoice')
        ->set('emailMessage', 'Hello.')
        ->call('sendInvoiceEmail')
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('validates emailTo, subject, and message', function () {
    Livewire::test(Form::class, ['id' => $this->invoice->id])
        ->call('openShareModal')
        ->set('emailTo', 'not-an-email')
        ->set('emailSubject', '')
        ->set('emailMessage', '')
        ->call('sendInvoiceEmail')
        ->assertHasErrors(['emailTo', 'emailSubject', 'emailMessage']);

    Mail::assertNothingSent();
});

it('actually renders the seller-typed custom message in the email body (regression)', function () {
    // Regression: the Mailable accepts $customMessage but its view didn't read
    // it. The seller types a message in the share modal, hits send, and the
    // customer gets the canned template instead — never seeing the seller's
    // greeting, link, or note. Pin behavior so the fix doesn't drift.
    Livewire::test(Form::class, ['id' => $this->invoice->id])
        ->call('openShareModal')
        ->set('emailTo', 'customer@example.com')
        ->set('emailSubject', 'Your invoice')
        ->set('emailMessage', 'CUSTOM-PHRASE-XYZZY-pay-by-Friday')
        ->call('sendInvoiceEmail');

    Mail::assertSent(InvoiceNotification::class, function ($mail) {
        // Render the mail body and search for our marker phrase. If the
        // view ignores customMessage, this fails.
        $rendered = (string) $mail->render();
        return str_contains($rendered, 'CUSTOM-PHRASE-XYZZY-pay-by-Friday');
    });
});

it('renders a public link in the email so the customer can view the invoice (regression)', function () {
    // Regression: the Mailable view rendered `$publicUrl` which it sourced
    // from `$invoice->public_url` — an attribute that never existed on the
    // Invoice model. Result: customers got an email with no link, nothing
    // to click, no attached PDF. They had to email back asking for the
    // invoice itself.
    Livewire::test(Form::class, ['id' => $this->invoice->id])
        ->call('openShareModal')
        ->set('emailTo', 'customer@example.com')
        ->set('emailSubject', 'Your invoice')
        ->set('emailMessage', 'Hello.')
        ->call('sendInvoiceEmail');

    Mail::assertSent(InvoiceNotification::class, function ($mail) {
        $rendered = (string) $mail->render();
        // Either the share link is rendered or, at minimum, the public-route
        // path is present.
        return str_contains($rendered, '/public/invoices/');
    });
});

it('does not send and does not crash when the invoice is gone before send (defensive)', function () {
    // Mail::fake() can't easily be made to throw, so trip the try/catch via
    // the most concrete path the action exposes: a vanished invoiceId.
    // What we care about regression-wise is "no mail goes out, no crash" —
    // not the exact session-flash mechanics (which Livewire's test client
    // makes awkward to inspect mid-cycle).
    $component = Livewire::test(Form::class, ['id' => $this->invoice->id])
        ->call('openShareModal')
        ->set('emailTo', 'customer@example.com')
        ->set('emailSubject', 'Your invoice')
        ->set('emailMessage', 'Hello.');

    $this->invoice->forceDelete(); // findOrFail throws → caught → no mail

    $component->call('sendInvoiceEmail');

    Mail::assertNothingSent();
});
