<?php

/**
 * Smoke for the customer-facing public invoice flow. Until this test
 * existed, the route was wired end-to-end but had zero coverage — and
 * the "Download" button on the public page was firing window.print()
 * instead of streaming a PDF (shipped that way for who knows how long).
 */

use App\Livewire\Public\Invoices\Show;
use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Sales\Customer;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->product  = Product::factory()->create(['name' => 'Widget Pro', 'sku' => 'WP-001']);

    $this->invoice = Invoice::factory()->create([
        'customer_id' => $this->customer->id,
        'subtotal' => 750000,
        'total'    => 750000,
        'status'   => 'sent',
    ]);
    InvoiceItem::create([
        'invoice_id'  => $this->invoice->id,
        'product_id'  => $this->product->id,
        'description' => 'Widget Pro',
        'quantity'    => 3,
        'unit_price'  => 250000,
        'total'       => 750000,
    ]);

    $this->invoice->ensureShareToken(true);
});

it('renders the invoice for a valid signed-URL token', function () {
    $url = URL::signedRoute('public.invoices.show', ['token' => $this->invoice->share_token]);

    $this->get($url)
        ->assertOk()
        ->assertSee($this->invoice->invoice_number);
});

it('rejects unsigned requests with 403', function () {
    $this->get(route('public.invoices.show', ['token' => $this->invoice->share_token]))
        ->assertForbidden();
});

it('shows the expired view for tokens whose expiry has passed', function () {
    $this->invoice->update(['share_token_expires_at' => now()->subDay()]);

    Livewire::test(Show::class, ['token' => $this->invoice->share_token])
        ->assertSet('expired', true)
        ->assertSet('invoice', null);
});

it('shows the expired view for unknown tokens', function () {
    Livewire::test(Show::class, ['token' => 'definitely-not-a-real-token'])
        ->assertSet('expired', true);
});

it('streams a PDF when the customer clicks Download', function () {
    // Regression: the Download button on the public page was wired to
    // window.print() instead of an actual download endpoint. This test
    // makes sure the action returns a real PDF response.
    $component = Livewire::test(Show::class, ['token' => $this->invoice->share_token]);

    $response = $component->instance()->downloadPdf();

    expect($response)->not->toBeNull();
    expect($response->headers->get('Content-Type'))->toContain('pdf');
    expect(strlen($response->getContent()))->toBeGreaterThan(1000);
});

it('returns null from downloadPdf when the invoice is expired', function () {
    $this->invoice->update(['share_token_expires_at' => now()->subDay()]);

    $response = Livewire::test(Show::class, ['token' => $this->invoice->share_token])
        ->instance()
        ->downloadPdf();

    expect($response)->toBeNull();
});
