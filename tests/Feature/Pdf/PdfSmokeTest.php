<?php

/**
 * PDF smoke test — every entry point in PdfService and PdfController must
 * render bytes without throwing, and must not leak unexpanded Blade
 * directives like `@formatCurrency` into the output (a real bug we shipped
 * for months on the invoice template before this test existed).
 */

use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\InvoiceItem;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\PurchaseRfqItem;
use App\Models\Purchase\Supplier;
use App\Models\Sales\Customer;
use App\Models\Sales\SalesOrder;
use App\Models\Sales\SalesOrderItem;
use App\Services\PdfService;

beforeEach(function () {
    $this->customer = Customer::factory()->create();
    $this->supplier = Supplier::factory()->create();
    $this->product  = Product::factory()->create(['name' => 'Widget Pro', 'sku' => 'WP-001']);
});

function makeSalesOrderWithItems(\App\Models\Sales\Customer $customer, \App\Models\Inventory\Product $product, string $status = 'draft'): SalesOrder
{
    $order = SalesOrder::factory()->create([
        'customer_id' => $customer->id,
        'status'      => $status,
    ]);
    SalesOrderItem::create([
        'sales_order_id' => $order->id,
        'product_id'     => $product->id,
        'quantity'       => 3,
        'quantity_invoiced'  => 0,
        'quantity_delivered' => 0,
        'unit_price'     => 250000,
        'discount'       => 0,
        'total'          => 750000,
    ]);
    $order->update(['subtotal' => 750000, 'total' => 750000]);

    return $order->fresh(['customer', 'items.product']);
}

it('generates an invoice PDF with line-item currency rendered (regression: @formatCurrency)', function () {
    $order = makeSalesOrderWithItems($this->customer, $this->product, 'processing');
    $invoice = Invoice::factory()->create([
        'customer_id'    => $this->customer->id,
        'sales_order_id' => $order->id,
        'subtotal' => 750000,
        'total'    => 750000,
        'status'   => 'sent',
    ]);
    InvoiceItem::create([
        'invoice_id'  => $invoice->id,
        'product_id'  => $this->product->id,
        'description' => $this->product->name,
        'quantity'    => 3,
        'unit_price'  => 250000,
        'total'       => 750000,
    ]);

    $response = PdfService::generateInvoice($invoice->fresh(['customer', 'items.product', 'payments']));
    $bytes    = $response->getContent();

    expect(strlen($bytes))->toBeGreaterThan(1000);
    expect($bytes)->not->toContain('@formatCurrency'); // shipped bug, never again
});

it('generates a sales-order PDF', function () {
    $order = makeSalesOrderWithItems($this->customer, $this->product, 'draft');

    $response = PdfService::generateSalesOrder($order);

    expect(strlen($response->getContent()))->toBeGreaterThan(1000);
    expect($response->getContent())->not->toContain('@formatCurrency');
});

it('renders raw sales-order PDF bytes (used by email-attach + custom stream)', function () {
    $order = makeSalesOrderWithItems($this->customer, $this->product, 'processing');

    $bytes = PdfService::renderSalesOrder($order);

    // Regression: the inline call site in Sales/Orders/Form.php previously passed
    // 'salesOrder' to a template that reads $order — the rendered PDF would crash
    // with "Undefined variable $order" on every email attach.
    expect(strlen($bytes))->toBeGreaterThan(1000);
});

it('generates a purchase-order PDF', function () {
    $rfq = PurchaseRfq::factory()->create([
        'supplier_id' => $this->supplier->id,
        'status'      => 'purchase_order',
    ]);
    PurchaseRfqItem::create([
        'purchase_rfq_id' => $rfq->id,
        'product_id'      => $this->product->id,
        'quantity'        => 5,
        'unit_price'      => 100000,
        'total'           => 500000,
    ]);
    $rfq->update(['subtotal' => 500000, 'total' => 500000]);

    $response = PdfService::generatePurchaseOrder($rfq->fresh(['supplier', 'items.product']));

    expect(strlen($response->getContent()))->toBeGreaterThan(1000);
    expect($response->getContent())->not->toContain('@formatCurrency');
});
