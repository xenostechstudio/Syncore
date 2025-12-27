<?php

namespace App\Http\Controllers;

use App\Models\Delivery\DeliveryOrder;
use App\Models\Invoicing\Invoice;
use App\Models\Sales\SalesOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function invoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'payments']);
        
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ]);
        
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function salesOrder(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.product', 'salesperson']);
        
        $pdf = Pdf::loadView('pdf.sales-order', [
            'order' => $salesOrder,
        ]);
        
        return $pdf->download("sales-order-{$salesOrder->order_number}.pdf");
    }

    public function deliveryOrder(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load(['salesOrder.customer', 'items.product', 'warehouse']);
        
        $pdf = Pdf::loadView('pdf.delivery-order', [
            'delivery' => $deliveryOrder,
        ]);
        
        return $pdf->download("delivery-order-{$deliveryOrder->delivery_number}.pdf");
    }
}
