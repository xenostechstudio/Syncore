<?php

namespace App\Http\Controllers;

use App\Models\Delivery\DeliveryOrder;
use App\Models\HR\PayrollItem;
use App\Models\Invoicing\Invoice;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\VendorBill;
use App\Models\Sales\SalesOrder;
use App\Models\Settings\InvoiceSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    /**
     * Sanitize filename by replacing invalid characters
     */
    protected function sanitizeFilename(string $filename): string
    {
        return str_replace(['/', '\\'], '-', $filename);
    }

    public function invoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'items.product', 'payments']);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ] + $this->brandContext());

        $filename = $this->sanitizeFilename("invoice-{$invoice->invoice_number}.pdf");
        return $pdf->download($filename);
    }

    public function salesOrder(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.product', 'salesperson']);

        $pdf = Pdf::loadView('pdf.sales-order', [
            'order' => $salesOrder,
        ] + $this->brandContext());

        $docType = in_array($salesOrder->status, ['draft', 'confirmed']) ? 'quotation' : 'sales-order';
        $filename = $this->sanitizeFilename("{$docType}-{$salesOrder->order_number}.pdf");
        return $pdf->download($filename);
    }

    public function deliveryOrder(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load(['salesOrder.customer', 'items.product', 'warehouse']);

        $pdf = Pdf::loadView('pdf.delivery-note', [
            'delivery' => $deliveryOrder,
        ] + $this->brandContext());

        $filename = $this->sanitizeFilename("delivery-note-{$deliveryOrder->delivery_number}.pdf");
        return $pdf->download($filename);
    }

    public function purchaseOrder(PurchaseRfq $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product']);

        $pdf = Pdf::loadView('pdf.purchase-order', [
            'order' => $purchaseOrder,
        ] + $this->brandContext());

        $docType = $purchaseOrder->status === 'purchase_order' ? 'PO' : 'RFQ';
        $filename = $this->sanitizeFilename("{$docType}-{$purchaseOrder->reference}.pdf");
        return $pdf->download($filename);
    }

    public function vendorBill(VendorBill $vendorBill)
    {
        $vendorBill->load(['supplier', 'items.product', 'payments']);

        $pdf = Pdf::loadView('pdf.vendor-bill', [
            'bill' => $vendorBill,
        ] + $this->brandContext());

        $filename = $this->sanitizeFilename("vendor-bill-{$vendorBill->bill_number}.pdf");
        return $pdf->download($filename);
    }

    public function payrollSlip(PayrollItem $payrollItem)
    {
        $user = auth()->user();
        $isOwner = $user && $payrollItem->employee?->user_id === $user->id;

        if (! $isOwner && ! $user?->can('payroll.view')) {
            abort(403);
        }

        $payrollItem->load(['employee.department', 'employee.position', 'period', 'details.component']);

        $pdf = Pdf::loadView('pdf.payroll-slip', [
            'payrollItem' => $payrollItem,
        ] + $this->brandContext());

        $employeeName = str_replace(' ', '-', strtolower($payrollItem->employee?->name ?? 'employee'));
        $periodName = str_replace(' ', '-', strtolower($payrollItem->period?->name ?? 'period'));

        $filename = $this->sanitizeFilename("payroll-slip-{$employeeName}-{$periodName}.pdf");
        return $pdf->download($filename);
    }

    /**
     * Brand context shared by every PDF: company info + InvoiceSetting.
     * The setting model is named for invoices but its fields (colors,
     * currency, watermark, date format) drive every document type — one
     * place to manage brand parity across the whole stack.
     */
    protected function brandContext(): array
    {
        $company = \App\Models\Settings\CompanyProfile::first();

        return [
            'company' => [
                'name'    => $company?->name    ?? config('app.name'),
                'address' => $company?->address ?? '',
                'phone'   => $company?->phone   ?? '',
                'email'   => $company?->email   ?? '',
                'website' => $company?->website ?? '',
                'logo'    => $company?->logo_path ?? null,
                'tax_id'  => $company?->tax_id  ?? '',
            ],
            'settings' => InvoiceSetting::instance(),
        ];
    }
}
