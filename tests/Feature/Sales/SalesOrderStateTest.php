<?php

use App\Enums\SalesOrderState;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\User;
use App\Services\SalesOrderService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create();
    $this->actingAs($this->user);
});

describe('SalesOrderState', function () {
    it('has correct labels', function () {
        expect(SalesOrderState::QUOTATION->label())->toBe('Quotation');
        expect(SalesOrderState::QUOTATION_SENT->label())->toBe('Quotation Sent');
        expect(SalesOrderState::SALES_ORDER->label())->toBe('Sales Order');
        expect(SalesOrderState::DONE->label())->toBe('Done');
        expect(SalesOrderState::CANCELLED->label())->toBe('Cancelled');
    });

    it('has correct colors', function () {
        expect(SalesOrderState::QUOTATION->color())->toBe('zinc');
        expect(SalesOrderState::QUOTATION_SENT->color())->toBe('blue');
        expect(SalesOrderState::SALES_ORDER->color())->toBe('amber');
        expect(SalesOrderState::DONE->color())->toBe('emerald');
        expect(SalesOrderState::CANCELLED->color())->toBe('red');
    });

    it('correctly identifies terminal states', function () {
        expect(SalesOrderState::QUOTATION->isTerminal())->toBeFalse();
        expect(SalesOrderState::SALES_ORDER->isTerminal())->toBeFalse();
        expect(SalesOrderState::DONE->isTerminal())->toBeTrue();
        expect(SalesOrderState::CANCELLED->isTerminal())->toBeTrue();
    });

    it('correctly identifies confirmable states', function () {
        expect(SalesOrderState::QUOTATION->canConfirm())->toBeTrue();
        expect(SalesOrderState::QUOTATION_SENT->canConfirm())->toBeTrue();
        expect(SalesOrderState::SALES_ORDER->canConfirm())->toBeFalse();
        expect(SalesOrderState::DONE->canConfirm())->toBeFalse();
    });

    it('correctly identifies invoice creation states', function () {
        expect(SalesOrderState::QUOTATION->canCreateInvoice())->toBeFalse();
        expect(SalesOrderState::SALES_ORDER->canCreateInvoice())->toBeTrue();
        expect(SalesOrderState::DONE->canCreateInvoice())->toBeFalse();
    });
});

describe('SalesOrder Model', function () {
    it('generates order number on create', function () {
        $order = SalesOrder::factory()->create();
        expect($order->order_number)->toStartWith('SO');
        expect($order->order_number)->toMatch('/^SO\d{5}$/');
    });

    it('returns correct state attribute', function () {
        $order = SalesOrder::factory()->create(['status' => 'draft']);
        expect($order->state)->toBe(SalesOrderState::QUOTATION);

        $order->update(['status' => 'processing']);
        expect($order->fresh()->state)->toBe(SalesOrderState::SALES_ORDER);
    });

    it('can transition to sales order state', function () {
        $order = SalesOrder::factory()->create(['status' => 'draft']);
        
        $result = $order->confirm();
        
        expect($result)->toBeTrue();
        expect($order->fresh()->state)->toBe(SalesOrderState::SALES_ORDER);
    });

    it('cannot confirm already confirmed order', function () {
        $order = SalesOrder::factory()->create(['status' => 'processing']);
        
        $result = $order->confirm();
        
        expect($result)->toBeFalse();
    });
});

describe('SalesOrderService', function () {
    it('confirms quotation to sales order', function () {
        $order = SalesOrder::factory()->create(['status' => 'draft']);

        $service = new SalesOrderService();
        $result = $service->confirm($order);

        expect($result)->toBeTrue();
        expect($order->fresh()->status)->toBe('processing');
    });

    it('sends quotation to customer', function () {
        $order = SalesOrder::factory()->create(['status' => 'draft']);

        $service = new SalesOrderService();
        $result = $service->sendQuotation($order);

        expect($result)->toBeTrue();
        expect($order->fresh()->status)->toBe('confirmed');
    });

    it('cancels sales order', function () {
        $order = SalesOrder::factory()->create(['status' => 'draft']);

        $service = new SalesOrderService();
        $result = $service->cancel($order, 'Customer requested cancellation');

        expect($result)->toBeTrue();
        expect($order->fresh()->status)->toBe('cancelled');
    });

    it('cannot cancel done order', function () {
        $order = SalesOrder::factory()->create(['status' => 'delivered']);

        $service = new SalesOrderService();
        $result = $service->cancel($order);

        expect($result)->toBeFalse();
    });
});
