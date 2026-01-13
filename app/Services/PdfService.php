<?php

namespace App\Services;

use App\Models\Delivery\DeliveryOrder;
use App\Models\HR\LeaveRequest;
use App\Models\Inventory\InventoryAdjustment;
use App\Models\Inventory\InventoryTransfer;
use App\Models\Invoicing\Invoice;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Sales\SalesOrder;
use App\Models\Settings\InvoiceSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

/**
 * PDF Service
 * 
 * Generates PDF documents for various business entities including
 * invoices, sales orders, purchase orders, delivery notes, and more.
 * Uses DomPDF for PDF generation.
 * 
 * @package App\Services
 */
class PdfService
{
    /**
     * Sanitize filename by replacing invalid characters.
     *
     * @param string $filename The filename to sanitize
     * @return string Sanitized filename
     */
    protected static function sanitizeFilename(string $filename): string
    {
        return str_replace(['/', '\\'], '-', $filename);
    }

    /**
     * Generate and download an invoice PDF.
     *
     * @param Invoice $invoice The invoice to generate PDF for
     * @return Response PDF download response
     */
    public static function generateInvoice(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'items.product', 'payments']);
        $settings = InvoiceSetting::instance();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => self::getCompanyInfo(),
            'settings' => $settings,
        ]);

        $filename = self::sanitizeFilename("Invoice-{$invoice->invoice_number}.pdf");
        return $pdf->download($filename);
    }

    /**
     * Generate and download a sales order/quotation PDF.
     *
     * @param SalesOrder $order The sales order to generate PDF for
     * @return Response PDF download response
     */
    public static function generateSalesOrder(SalesOrder $order): Response
    {
        $order->load(['customer', 'items.product']);

        $pdf = Pdf::loadView('pdf.sales-order', [
            'order' => $order,
            'company' => self::getCompanyInfo(),
        ]);

        $docType = in_array($order->status, ['draft', 'confirmed']) ? 'Quotation' : 'SalesOrder';
        $filename = self::sanitizeFilename("{$docType}-{$order->order_number}.pdf");
        return $pdf->download($filename);
    }

    /**
     * Generate and download a purchase order/RFQ PDF.
     *
     * @param PurchaseRfq $order The purchase RFQ to generate PDF for
     * @return Response PDF download response
     */
    public static function generatePurchaseOrder(PurchaseRfq $order): Response
    {
        $order->load(['supplier', 'items.product']);

        $pdf = Pdf::loadView('pdf.purchase-order', [
            'order' => $order,
            'company' => self::getCompanyInfo(),
        ]);

        $docType = $order->status === 'purchase_order' ? 'PO' : 'RFQ';
        $filename = self::sanitizeFilename("{$docType}-{$order->reference}.pdf");
        return $pdf->download($filename);
    }

    /**
     * Generate and download a delivery note PDF.
     *
     * @param DeliveryOrder $delivery The delivery order to generate PDF for
     * @return Response PDF download response
     */
    public static function generateDeliveryNote(DeliveryOrder $delivery): Response
    {
        $delivery->load(['salesOrder.customer', 'items.product', 'warehouse']);

        $pdf = Pdf::loadView('pdf.delivery-note', [
            'delivery' => $delivery,
            'company' => self::getCompanyInfo(),
        ]);

        $filename = self::sanitizeFilename("DeliveryNote-{$delivery->delivery_number}.pdf");
        return $pdf->download($filename);
    }

    /**
     * Generate and download a leave request PDF.
     *
     * @param LeaveRequest $leaveRequest The leave request to generate PDF for
     * @return Response PDF download response
     */
    public static function generateLeaveRequest(LeaveRequest $leaveRequest): Response
    {
        $leaveRequest->load(['employee', 'leaveType', 'approver']);

        $pdf = Pdf::loadView('pdf.leave-request', [
            'leaveRequest' => $leaveRequest,
            'company' => self::getCompanyInfo(),
        ]);

        return $pdf->download("LeaveRequest-{$leaveRequest->id}.pdf");
    }

    /**
     * Generate and download an inventory transfer PDF.
     *
     * @param InventoryTransfer $transfer The inventory transfer to generate PDF for
     * @return Response PDF download response
     */
    public static function generateInventoryTransfer(InventoryTransfer $transfer): Response
    {
        $transfer->load(['sourceWarehouse', 'destinationWarehouse', 'items.product']);

        $pdf = Pdf::loadView('pdf.inventory-transfer', [
            'transfer' => $transfer,
            'company' => self::getCompanyInfo(),
        ]);

        $filename = self::sanitizeFilename("Transfer-{$transfer->transfer_number}.pdf");
        return $pdf->download($filename);
    }

    /**
     * Generate and download an inventory adjustment PDF.
     *
     * @param InventoryAdjustment $adjustment The inventory adjustment to generate PDF for
     * @return Response PDF download response
     */
    public static function generateInventoryAdjustment(InventoryAdjustment $adjustment): Response
    {
        $adjustment->load(['warehouse', 'items.product']);

        $pdf = Pdf::loadView('pdf.inventory-adjustment', [
            'adjustment' => $adjustment,
            'company' => self::getCompanyInfo(),
        ]);

        $filename = self::sanitizeFilename("Adjustment-{$adjustment->reference}.pdf");
        return $pdf->download($filename);
    }

    /**
     * Generate and download a purchase RFQ PDF.
     *
     * @param PurchaseRfq $rfq The purchase RFQ to generate PDF for
     * @return Response PDF download response
     */
    public static function generatePurchaseRfq(PurchaseRfq $rfq): Response
    {
        $rfq->load(['supplier', 'items.product']);

        $pdf = Pdf::loadView('pdf.purchase-rfq', [
            'rfq' => $rfq,
            'company' => self::getCompanyInfo(),
        ]);

        $filename = self::sanitizeFilename("RFQ-{$rfq->reference}.pdf");
        return $pdf->download($filename);
    }

    /**
     * Stream an invoice PDF (view in browser).
     *
     * @param Invoice $invoice The invoice to stream
     * @return Response PDF stream response
     */
    public static function streamInvoice(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'items.product', 'payments']);
        $settings = InvoiceSetting::instance();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => self::getCompanyInfo(),
            'settings' => $settings,
        ]);

        $filename = self::sanitizeFilename("Invoice-{$invoice->invoice_number}.pdf");
        return $pdf->stream($filename);
    }

    /**
     * Stream a sales order PDF (view in browser).
     *
     * @param SalesOrder $order The sales order to stream
     * @return Response PDF stream response
     */
    public static function streamSalesOrder(SalesOrder $order): Response
    {
        $order->load(['customer', 'items.product']);

        $pdf = Pdf::loadView('pdf.sales-order', [
            'order' => $order,
            'company' => self::getCompanyInfo(),
        ]);

        $filename = self::sanitizeFilename("Order-{$order->order_number}.pdf");
        return $pdf->stream($filename);
    }

    /**
     * Get company information for PDF headers.
     *
     * @return array{name: string, address: string, phone: string, email: string, website: string, logo: string|null, tax_id: string}
     */
    protected static function getCompanyInfo(): array
    {
        $company = \App\Models\Settings\CompanyProfile::first();

        return [
            'name' => $company?->name ?? config('app.name'),
            'address' => $company?->address ?? '',
            'phone' => $company?->phone ?? '',
            'email' => $company?->email ?? '',
            'website' => $company?->website ?? '',
            'logo' => $company?->logo_path ?? null,
            'tax_id' => $company?->tax_id ?? '',
        ];
    }
}
