<?php

/**
 * Coverage for the Xendit payment webhook. Before this suite the controller
 * + handleWebhook() were entirely untested and shipped with:
 *  - LIKE+amount duplicate detection (mismatched coincidental payments,
 *    raced under concurrent webhook delivery)
 *  - paid_amount computed pre-insert so two interleaved deliveries could
 *    each compute "old + this one" and double-count
 *  - empty webhook token silently bypassing signature verification in any
 *    environment, including production
 *  - full webhook payload logged at INFO level
 *
 * These tests pin the new behavior so we don't regress.
 */

use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use App\Models\Sales\Customer;

beforeEach(function () {
    config(['xendit.webhook_token' => 'test-token-secret']);

    $this->customer = Customer::factory()->create();
    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'subtotal'    => 1_000_000,
        'total'       => 1_000_000,
        'status'      => 'sent',
        'paid_amount' => 0,
    ]);
});

function xenditPaidPayload(int $invoiceId, float $amount = 1_000_000, ?string $xenditId = null): array
{
    return [
        'id'              => $xenditId ?? 'xendit-payment-' . uniqid(),
        'external_id'     => "INV-{$invoiceId}-" . time(),
        'status'          => 'PAID',
        'paid_amount'     => $amount,
        'payment_method'  => 'BANK_TRANSFER',
        'payment_channel' => 'BCA',
    ];
}

it('records a payment + marks the invoice paid on a valid PAID webhook', function () {
    $payload = xenditPaidPayload($this->invoice->id);

    $this->postJson('/api/webhooks/xendit/invoice', $payload, ['x-callback-token' => 'test-token-secret'])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->invoice->refresh();
    expect($this->invoice->status)->toBe('paid');
    expect((float) $this->invoice->paid_amount)->toBe(1_000_000.0);
    expect(Payment::where('invoice_id', $this->invoice->id)->count())->toBe(1);
    expect(Payment::where('reference', 'XENDIT-' . $payload['id'])->exists())->toBeTrue();
});

it('is idempotent: a duplicate webhook with the same payment id creates no second Payment row', function () {
    // Regression: previously the duplicate-check used reference LIKE 'XENDIT-%'
    // AND amount = X, which raced and could mis-match. Now reference is exact.
    $payload = xenditPaidPayload($this->invoice->id);

    $this->postJson('/api/webhooks/xendit/invoice', $payload, ['x-callback-token' => 'test-token-secret'])->assertOk();
    $this->postJson('/api/webhooks/xendit/invoice', $payload, ['x-callback-token' => 'test-token-secret'])->assertOk();
    $this->postJson('/api/webhooks/xendit/invoice', $payload, ['x-callback-token' => 'test-token-secret'])->assertOk();

    expect(Payment::where('invoice_id', $this->invoice->id)->count())->toBe(1);
    expect((float) $this->invoice->fresh()->paid_amount)->toBe(1_000_000.0);
});

it('does not double-count a coincidental same-amount manual payment', function () {
    // Operator entered a manual payment of the same amount before the
    // webhook fired. The old LIKE+amount check would have matched this
    // unrelated row and silently skipped recording the actual Xendit
    // payment. Now reference is unique to Xendit's payment id.
    Payment::create([
        'payment_number' => 'PAY/' . now()->year . '/00001',
        'invoice_id'     => $this->invoice->id,
        'amount'         => 1_000_000,
        'payment_date'   => now(),
        'payment_method' => 'Cash',
        'reference'      => 'MANUAL-001',
        'status'         => 'completed',
    ]);

    $payload = xenditPaidPayload($this->invoice->id);
    $this->postJson('/api/webhooks/xendit/invoice', $payload, ['x-callback-token' => 'test-token-secret'])
        ->assertOk();

    // Both payments persist; the Xendit one was correctly recorded
    // alongside the manual one rather than being mistaken for it.
    expect(Payment::where('invoice_id', $this->invoice->id)->count())->toBe(2);
    expect(Payment::where('reference', 'XENDIT-' . $payload['id'])->exists())->toBeTrue();
});

it('rejects requests with an invalid x-callback-token (401)', function () {
    $this->postJson(
        '/api/webhooks/xendit/invoice',
        xenditPaidPayload($this->invoice->id),
        ['x-callback-token' => 'WRONG-TOKEN']
    )->assertStatus(401);

    expect(Payment::count())->toBe(0);
});

it('refuses to process when production has no webhook token configured (503)', function () {
    // Regression: previously an empty config allowed every request through
    // unchallenged. In production that's an open endpoint anyone can spam
    // with fabricated PAID payloads.
    config(['xendit.webhook_token' => null]);
    app()->detectEnvironment(fn () => 'production');

    $this->postJson('/api/webhooks/xendit/invoice', xenditPaidPayload($this->invoice->id))
        ->assertStatus(503)
        ->assertJson(['error' => 'Webhook not configured']);

    expect(Payment::count())->toBe(0);
});

it('still accepts unsigned webhooks in non-production environments', function () {
    // Local dev convenience: lets you POST from curl without faking the
    // Xendit dashboard's token. Logs a warning so it's still visible.
    config(['xendit.webhook_token' => null]);
    // Default env in tests is 'testing' — leave as-is.

    $this->postJson('/api/webhooks/xendit/invoice', xenditPaidPayload($this->invoice->id))
        ->assertOk();

    expect(Payment::where('invoice_id', $this->invoice->id)->count())->toBe(1);
});

it('marks the invoice xendit_status=expired on EXPIRED status', function () {
    $this->postJson('/api/webhooks/xendit/invoice', [
        'id'          => 'xendit-test-id',
        'external_id' => "INV-{$this->invoice->id}-" . time(),
        'status'      => 'EXPIRED',
    ], ['x-callback-token' => 'test-token-secret'])->assertOk();

    expect($this->invoice->fresh()->xendit_status)->toBe('expired');
    expect(Payment::count())->toBe(0); // no Payment row created
});

it('acknowledges Xendit dashboard test payloads (non-INV external_id) without processing', function () {
    $this->postJson('/api/webhooks/xendit/invoice', [
        'external_id' => 'demo-test-12345',
        'status'      => 'PAID',
        'paid_amount' => 9999,
    ], ['x-callback-token' => 'test-token-secret'])
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(Payment::count())->toBe(0);
});

it('acknowledges empty bodies (Xendit "Test endpoint" button)', function () {
    $this->postJson('/api/webhooks/xendit/invoice', [], ['x-callback-token' => 'test-token-secret'])
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('records partial payments correctly when paid_amount is less than total', function () {
    $payload = xenditPaidPayload($this->invoice->id, amount: 400_000);

    $this->postJson('/api/webhooks/xendit/invoice', $payload, ['x-callback-token' => 'test-token-secret'])
        ->assertOk();

    $this->invoice->refresh();
    expect($this->invoice->status)->toBe('partial');
    expect((float) $this->invoice->paid_amount)->toBe(400_000.0);
});
