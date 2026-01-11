<?php

use App\Enums\InvoiceState;
use App\Models\Invoicing\Invoice;
use App\Models\Sales\Customer;
use App\Models\User;
use App\Services\InvoiceService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create();
    $this->actingAs($this->user);
});

describe('InvoiceState', function () {
    it('has correct labels', function () {
        expect(InvoiceState::DRAFT->label())->toBe('Draft');
        expect(InvoiceState::SENT->label())->toBe('Sent');
        expect(InvoiceState::PAID->label())->toBe('Paid');
        expect(InvoiceState::PARTIAL->label())->toBe('Partially Paid');
        expect(InvoiceState::OVERDUE->label())->toBe('Overdue');
        expect(InvoiceState::CANCELLED->label())->toBe('Cancelled');
    });

    it('has correct colors', function () {
        expect(InvoiceState::DRAFT->color())->toBe('zinc');
        expect(InvoiceState::SENT->color())->toBe('blue');
        expect(InvoiceState::PAID->color())->toBe('emerald');
        expect(InvoiceState::PARTIAL->color())->toBe('amber');
        expect(InvoiceState::OVERDUE->color())->toBe('red');
        expect(InvoiceState::CANCELLED->color())->toBe('zinc');
    });

    it('correctly identifies terminal states', function () {
        expect(InvoiceState::DRAFT->isTerminal())->toBeFalse();
        expect(InvoiceState::SENT->isTerminal())->toBeFalse();
        expect(InvoiceState::PAID->isTerminal())->toBeTrue();
        expect(InvoiceState::CANCELLED->isTerminal())->toBeTrue();
    });

    it('correctly identifies editable states', function () {
        expect(InvoiceState::DRAFT->canEdit())->toBeTrue();
        expect(InvoiceState::SENT->canEdit())->toBeFalse();
        expect(InvoiceState::PAID->canEdit())->toBeFalse();
    });

    it('correctly identifies sendable states', function () {
        expect(InvoiceState::DRAFT->canSend())->toBeTrue();
        expect(InvoiceState::SENT->canSend())->toBeFalse();
        expect(InvoiceState::PAID->canSend())->toBeFalse();
    });

    it('correctly identifies payment registration states', function () {
        expect(InvoiceState::DRAFT->canRegisterPayment())->toBeFalse();
        expect(InvoiceState::SENT->canRegisterPayment())->toBeTrue();
        expect(InvoiceState::PARTIAL->canRegisterPayment())->toBeTrue();
        expect(InvoiceState::OVERDUE->canRegisterPayment())->toBeTrue();
        expect(InvoiceState::PAID->canRegisterPayment())->toBeFalse();
    });
});

describe('Invoice Model', function () {
    it('generates invoice number on creation', function () {
        $invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'draft',
            'subtotal' => 100,
            'tax' => 10,
            'total' => 110,
        ]);

        expect($invoice->invoice_number)->toStartWith('INV/' . now()->year . '/');
    });

    it('returns correct state attribute', function () {
        $invoice = Invoice::factory()->create(['status' => 'draft']);
        expect($invoice->state)->toBe(InvoiceState::DRAFT);

        $invoice->update(['status' => 'sent']);
        expect($invoice->fresh()->state)->toBe(InvoiceState::SENT);
    });
});

describe('InvoiceService', function () {
    it('registers payment and updates status to partial', function () {
        $invoice = Invoice::factory()->create([
            'status' => 'sent',
            'total' => 1000,
            'paid_amount' => 0,
        ]);

        $service = new InvoiceService();
        $payment = $service->registerPayment($invoice, 500);

        expect($payment->amount)->toBe(500.0);
        expect($invoice->fresh()->status)->toBe('partial');
        expect($invoice->fresh()->paid_amount)->toBe(500.0);
    });

    it('registers payment and updates status to paid when fully paid', function () {
        $invoice = Invoice::factory()->create([
            'status' => 'sent',
            'total' => 1000,
            'paid_amount' => 0,
        ]);

        $service = new InvoiceService();
        $payment = $service->registerPayment($invoice, 1000);

        expect($invoice->fresh()->status)->toBe('paid');
        expect($invoice->fresh()->paid_date)->not->toBeNull();
    });

    it('sends invoice and updates status', function () {
        $invoice = Invoice::factory()->create(['status' => 'draft']);

        $service = new InvoiceService();
        $result = $service->send($invoice);

        expect($result)->toBeTrue();
        expect($invoice->fresh()->status)->toBe('sent');
    });

    it('cannot send non-draft invoice', function () {
        $invoice = Invoice::factory()->create(['status' => 'sent']);

        $service = new InvoiceService();
        $result = $service->send($invoice);

        expect($result)->toBeFalse();
    });
});
