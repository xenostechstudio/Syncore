<?php

/**
 * Smoke for the public sales-order/quotation share flow. Same shape as
 * PublicInvoiceShowTest — the route was live but uncovered, and the
 * Download button was firing window.print() instead of streaming a PDF.
 */

use App\Livewire\Public\SalesOrders\Show;
use App\Models\Inventory\Product;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->product  = Product::factory()->create(['name' => 'Widget Pro', 'sku' => 'WP-001']);

    $this->order = SalesOrder::factory()->create([
        'customer_id' => $this->customer->id,
        'status'      => 'draft',
    ]);
    SalesOrderItem::create([
        'sales_order_id' => $this->order->id,
        'product_id'     => $this->product->id,
        'quantity'       => 2,
        'quantity_invoiced'  => 0,
        'quantity_delivered' => 0,
        'unit_price'     => 100000,
        'discount'       => 0,
        'total'          => 200000,
    ]);
    $this->order->update(['subtotal' => 200000, 'total' => 200000]);

    // SalesOrder doesn't have an ensureShareToken helper yet — set the
    // fields directly. Mirrors what the Form's "share" action does.
    $this->order->update([
        'share_token'              => Str::random(48),
        'share_token_expires_at'   => now()->addDays(30),
    ]);
    $this->order->refresh();
});

it('renders the order for a valid signed-URL token', function () {
    $url = URL::signedRoute('public.sales-orders.show', ['token' => $this->order->share_token]);

    $this->get($url)
        ->assertOk()
        ->assertSee($this->order->order_number);
});

it('rejects unsigned requests with 403', function () {
    $this->get(route('public.sales-orders.show', ['token' => $this->order->share_token]))
        ->assertForbidden();
});

it('shows the expired view for past-expiry tokens', function () {
    $this->order->update(['share_token_expires_at' => now()->subDay()]);

    Livewire::test(Show::class, ['token' => $this->order->share_token])
        ->assertSet('expired', true)
        ->assertSet('order', null);
});

it('streams a PDF when the customer clicks Download', function () {
    // Regression: the Download button was wired to window.print().
    $response = Livewire::test(Show::class, ['token' => $this->order->share_token])
        ->instance()
        ->downloadPdf();

    expect($response)->not->toBeNull();
    expect($response->headers->get('Content-Type'))->toContain('pdf');
    expect(strlen($response->getContent()))->toBeGreaterThan(1000);
});
