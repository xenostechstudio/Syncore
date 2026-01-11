<?php

namespace App\Http\Controllers;

use App\Models\Delivery\DeliveryOrder;
use App\Models\HR\PayrollItem;
use App\Models\Invoicing\Invoice;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\VendorBill;
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
            'company' => $this->getCompanyInfo(),
        ]);
        
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function salesOrder(SalesOrder $salesOrder)
    {
        $salesOrder->load(['customer', 'items.product', 'salesperson']);
        
        $pdf = Pdf::loadView('pdf.sales-order', [
            'order' => $salesOrder,
            'company' => $this->getCompanyInfo(),
        ]);
        
        $docType = in_array($salesOrder->status, ['draft', 'confirmed']) ? 'quotation' : 'sales-order';
        return $pdf->download("{$docType}-{$salesOrder->order_number}.pdf");
    }

    public function deliveryOrder(DeliveryOrder $deliveryOrder)
    {
        $deliveryOrder->load(['salesOrder.customer', 'items.product', 'warehouse']);
        
        $pdf = Pdf::loadView('pdf.delivery-note', [
            'delivery' => $deliveryOrder,
            'company' => $this->getCompanyInfo(),
        ]);
        
        return $pdf->download("delivery-note-{$deliveryOrder->delivery_number}.pdf");
    }

    public function purchaseOrder(PurchaseRfq $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product']);
        
        $pdf = Pdf::loadView('pdf.purchase-order', [
            'order' => $purchaseOrder,
            'company' => $this->getCompanyInfo(),
        ]);
        
        $docType = $purchaseOrder->status === 'purchase_order' ? 'PO' : 'RFQ';
        return $pdf->download("{$docType}-{$purchaseOrder->reference}.pdf");
    }

    public function vendorBill(VendorBill $vendorBill)
    {
        $vendorBill->load(['supplier', 'items.product', 'payments']);
        
        $pdf = Pdf::loadView('pdf.vendor-bill', [
            'bill' => $vendorBill,
            'company' => $this->getCompanyInfo(),
        ]);
        
        return $pdf->download("vendor-bill-{$vendorBill->bill_number}.pdf");
    }

    public function payrollSlip(PayrollItem $payrollItem)
    {
        $payrollItem->load(['employee.department', 'employee.position', 'period', 'details.component']);
        
        $pdf = Pdf::loadView('pdf.payroll-slip', [
            'payrollItem' => $payrollItem,
            'company' => $this->getCompanyInfo(),
        ]);
        
        $employeeName = str_replace(' ', '-', strtolower($payrollItem->employee?->name ?? 'employee'));
        $periodName = str_replace(' ', '-', strtolower($payrollItem->period?->name ?? 'period'));
        
        return $pdf->download("payroll-slip-{$employeeName}-{$periodName}.pdf");
    }

    protected function getCompanyInfo(): array
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
