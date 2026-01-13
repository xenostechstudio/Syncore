<?php

use App\Models\User;
use App\Models\Sales\Customer;
use App\Models\Invoicing\Invoice;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->customer = Customer::factory()->create();
});

test('unauthenticated user cannot access invoices', function () {
    $response = $this->getJson('/api/v1/invoices');

    $response->assertStatus(401);
});

test('can list invoices', function () {
    Invoice::factory()->count(5)->create([
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/invoices');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
            'meta',
        ])
        ->assertJson(['success' => true]);
});

test('can filter invoices by customer', function () {
    $customer2 = Customer::factory()->create();
    
    Invoice::factory()->count(3)->create(['customer_id' => $this->customer->id]);
    Invoice::factory()->count(2)->create(['customer_id' => $customer2->id]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/invoices?customer_id={$this->customer->id}");

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(3);
});

test('can filter invoices by status', function () {
    Invoice::factory()->count(3)->create([
        'customer_id' => $this->customer->id,
        'status' => 'sent',
    ]);
    Invoice::factory()->count(2)->create([
        'customer_id' => $this->customer->id,
        'status' => 'paid',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/invoices?status=sent');

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(3);
});

test('can filter invoices by date range', function () {
    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'invoice_date' => '2026-01-15',
    ]);
    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'invoice_date' => '2026-02-15',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/invoices?date_from=2026-01-01&date_to=2026-01-31');

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBe(1);
});

test('can filter overdue invoices', function () {
    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => 'overdue',
    ]);
    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => 'paid',
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/invoices?overdue=1');

    $response->assertStatus(200);
    expect($response->json('meta.total'))->toBeGreaterThanOrEqual(1);
});

test('can get single invoice', function () {
    $invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson("/api/v1/invoices/{$invoice->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'id' => $invoice->id,
            ],
        ]);
});

test('returns 404 for non-existent invoice', function () {
    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/invoices/99999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Invoice not found',
        ]);
});

test('can get invoice summary', function () {
    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => 'sent',
        'total' => 1000000,
        'paid_amount' => 0,
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => 'overdue',
        'total' => 500000,
        'paid_amount' => 0,
    ]);

    Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => 'paid',
        'total' => 750000,
        'paid_amount' => 750000,
        'paid_date' => now(),
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/v1/invoices/summary');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_outstanding',
                'overdue_amount',
                'paid_this_month',
                'by_status',
            ],
        ]);
});
