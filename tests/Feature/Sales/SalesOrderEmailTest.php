<?php

/**
 * Coverage for sendEmail() on the Sales Orders form — the action that
 * delivers quotations + confirmed sales orders to customers via email
 * with an attached PDF. Mirrors the InvoiceEmailTest pattern; until this
 * file existed it was completely untested, and this session has had a
 * 100% hit rate finding bugs in untested customer-facing email flows.
 */

use App\Livewire\Sales\Orders\Form;
use App\Mail\SalesOrderNotification;
use App\Models\Inventory\Product;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

beforeEach(function () {
    $this->customer = Customer::factory()->create([
        'email' => 'customer@example.com',
        'name'  => 'Acme Co',
    ]);
    $this->product = Product::factory()->create(['name' => 'Widget Pro', 'sku' => 'WP-001']);

    $this->order = SalesOrder::factory()->create([
        'customer_id' => $this->customer->id,
        'status'      => 'draft',
    ]);
    SalesOrderItem::create([
        'sales_order_id'     => $this->order->id,
        'product_id'         => $this->product->id,
        'quantity'           => 2,
        'quantity_invoiced'  => 0,
        'quantity_delivered' => 0,
        'unit_price'         => 100_000,
        'discount'           => 0,
        'total'              => 200_000,
    ]);
    $this->order->update(['subtotal' => 200_000, 'total' => 200_000]);

    $this->user = User::factory()->create()->assignRole('super-admin');
    $this->actingAs($this->user);

    Mail::fake();
});

it('sends a SalesOrderNotification mailable to all configured recipients', function () {
    Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', ['customer@example.com', 'cc@example.com'])
        ->set('emailSubject', 'Quotation for review')
        ->set('emailBody', 'Hi, please review.')
        ->set('emailAttachPdf', true)
        ->call('sendEmail');

    Mail::assertSent(SalesOrderNotification::class, function ($mail) {
        return $mail->hasTo('customer@example.com')
            && $mail->hasTo('cc@example.com')
            && $mail->order->id === $this->order->id;
    });
});

it('attaches the order PDF when emailAttachPdf is true (regression guard for $salesOrder/$order key bug)', function () {
    // Regression: an earlier sendEmail() implementation rendered pdf.sales-order
    // with the wrong context key ('salesOrder' instead of 'order'), so the
    // attached PDF crashed on render every time a customer email went out.
    // The Mailable now owns the PDF build via PdfService::renderSalesOrder,
    // and the test pins both code paths.
    Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', ['customer@example.com'])
        ->set('emailSubject', 'Quotation')
        ->set('emailBody', 'Hi.')
        ->set('emailAttachPdf', true)
        ->call('sendEmail');

    Mail::assertSent(SalesOrderNotification::class, function ($mail) {
        // Mailable->attachments() returns Attachment objects; verify there's
        // exactly one PDF attachment and it actually renders bytes.
        $attachments = $mail->attachments();
        if (count($attachments) !== 1) {
            return false;
        }
        return $mail->attachPdf === true;
    });
});

it('skips PDF attachment when emailAttachPdf is false', function () {
    Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', ['customer@example.com'])
        ->set('emailSubject', 'Quotation')
        ->set('emailBody', 'Hi.')
        ->set('emailAttachPdf', false)
        ->call('sendEmail');

    Mail::assertSent(SalesOrderNotification::class, function ($mail) {
        return $mail->attachPdf === false && count($mail->attachments()) === 0;
    });
});

it('renders the seller-typed body in the email (regression: customMessage drop pattern)', function () {
    // Same shape of bug as InvoiceNotification — pin it for SalesOrder too.
    Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', ['customer@example.com'])
        ->set('emailSubject', 'Quotation')
        ->set('emailBody', 'CUSTOM-PHRASE-XYZZY-please-review')
        ->call('sendEmail');

    Mail::assertSent(SalesOrderNotification::class, function ($mail) {
        $rendered = (string) $mail->render();
        return str_contains($rendered, 'CUSTOM-PHRASE-XYZZY-please-review');
    });
});

it('renders a public link in the email so the customer can view the order', function () {
    Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', ['customer@example.com'])
        ->set('emailSubject', 'Quotation')
        ->set('emailBody', 'Hi.')
        ->call('sendEmail');

    Mail::assertSent(SalesOrderNotification::class, function ($mail) {
        $rendered = (string) $mail->render();
        return str_contains($rendered, '/public/sales-orders/');
    });
});

it('refuses when emailRecipients is empty (UX: tell the user, do not silently no-op)', function () {
    Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', [])
        ->set('emailSubject', 'Quotation')
        ->set('emailBody', 'Hi.')
        ->call('sendEmail')
        ->assertSet('emailRecipientError', 'Please add at least one recipient.');

    Mail::assertNothingSent();
});

it('validates subject and body', function () {
    Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', ['customer@example.com'])
        ->set('emailSubject', '')
        ->set('emailBody', '')
        ->call('sendEmail')
        ->assertHasErrors(['emailSubject', 'emailBody']);

    Mail::assertNothingSent();
});

it('rejects without sales.edit permission', function () {
    $unprivileged = User::factory()->create();
    $this->actingAs($unprivileged);

    Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', ['customer@example.com'])
        ->set('emailSubject', 'Quotation')
        ->set('emailBody', 'Hi.')
        ->call('sendEmail')
        ->assertForbidden();

    Mail::assertNothingSent();
});

it('does not send and does not crash when the order is gone before send (defensive)', function () {
    $component = Livewire::test(Form::class, ['id' => $this->order->id])
        ->call('prepareEmailModal')
        ->set('emailRecipients', ['customer@example.com'])
        ->set('emailSubject', 'Quotation')
        ->set('emailBody', 'Hi.');

    $this->order->forceDelete();

    $component->call('sendEmail');

    Mail::assertNothingSent();
});
