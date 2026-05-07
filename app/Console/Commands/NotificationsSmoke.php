<?php

namespace App\Console\Commands;

use App\Events\InvoiceOverdue;
use App\Events\InvoicePaid;
use App\Events\LowStockDetected;
use App\Events\OpportunityLost;
use App\Events\OpportunityWon;
use App\Events\PayrollProcessed;
use App\Events\PurchaseOrderReceived;
use App\Events\VendorBillPaid;
use App\Models\CRM\Opportunity;
use App\Models\HR\PayrollPeriod;
use App\Models\Inventory\Product;
use App\Models\Inventory\Warehouse;
use App\Models\Invoicing\Invoice;
use App\Models\Invoicing\Payment;
use App\Models\Purchase\PurchaseRfq;
use App\Models\Purchase\VendorBill;
use App\Models\Purchase\VendorBillPayment;
use App\Models\SystemNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class NotificationsSmoke extends Command
{
    protected $signature = 'notifications:smoke
        {--clear : Delete existing notifications first}';

    protected $description = 'Fire one of each wired event so the bell icon gets populated. Useful for eyeballing the dropdown after wiring or styling work.';

    public function handle(): int
    {
        if ($this->option('clear')) {
            $deleted = SystemNotification::query()->delete();
            $this->info("Cleared {$deleted} existing notifications.");
        }

        $before = SystemNotification::count();

        $this->fireOpportunityWon();
        $this->fireOpportunityLost();
        $this->fireInvoicePaid();
        $this->fireInvoiceOverdue();
        $this->firePayrollProcessed();
        $this->firePurchaseOrderReceived();
        $this->fireVendorBillPaid();
        $this->fireLowStockDetected();

        // The dropdown's unread count is cached for 60s — flush so refresh
        // shows the new entries immediately.
        Cache::flush();

        $after = SystemNotification::count();
        $this->info(($after - $before) . " new notifications written. Open the bell icon to see them.");

        return Command::SUCCESS;
    }

    private function fireOpportunityWon(): void
    {
        if ($opp = Opportunity::first()) {
            OpportunityWon::dispatch($opp);
            $this->line('  ✓ OpportunityWon');
        } else {
            $this->warn('  · skipped OpportunityWon (no Opportunity row)');
        }
    }

    private function fireOpportunityLost(): void
    {
        if ($opp = Opportunity::first()) {
            OpportunityLost::dispatch($opp, 'Smoke test');
            $this->line('  ✓ OpportunityLost');
        }
    }

    private function fireInvoicePaid(): void
    {
        $invoice = Invoice::with('payments')->whereHas('payments')->first()
            ?? Invoice::first();
        if (! $invoice) {
            $this->warn('  · skipped InvoicePaid (no Invoice row)');
            return;
        }
        $payment = $invoice->payments->first()
            ?? Payment::create([
                'payment_number' => 'SMOKE-' . $invoice->id,
                'invoice_id'     => $invoice->id,
                'payment_date'   => now(),
                'amount'         => $invoice->total,
                'payment_method' => 'Smoke',
                'status'         => 'completed',
            ]);
        InvoicePaid::dispatch($invoice, $payment);
        $this->line('  ✓ InvoicePaid');
    }

    private function fireInvoiceOverdue(): void
    {
        if ($invoice = Invoice::first()) {
            InvoiceOverdue::dispatch($invoice);
            $this->line('  ✓ InvoiceOverdue');
        }
    }

    private function firePayrollProcessed(): void
    {
        if ($period = PayrollPeriod::first()) {
            PayrollProcessed::dispatch($period);
            $this->line('  ✓ PayrollProcessed');
        } else {
            $this->warn('  · skipped PayrollProcessed (no PayrollPeriod row)');
        }
    }

    private function firePurchaseOrderReceived(): void
    {
        if ($rfq = PurchaseRfq::first()) {
            PurchaseOrderReceived::dispatch($rfq);
            $this->line('  ✓ PurchaseOrderReceived');
        } else {
            $this->warn('  · skipped PurchaseOrderReceived (no PurchaseRfq row)');
        }
    }

    private function fireVendorBillPaid(): void
    {
        $bill = VendorBill::first();
        if (! $bill) {
            $this->warn('  · skipped VendorBillPaid (no VendorBill row)');
            return;
        }
        $payment = VendorBillPayment::where('vendor_bill_id', $bill->id)->first()
            ?? VendorBillPayment::create([
                'vendor_bill_id' => $bill->id,
                'payment_date'   => now(),
                'amount'         => $bill->total,
                'payment_method' => 'Smoke',
            ]);
        VendorBillPaid::dispatch($bill, $payment);
        $this->line('  ✓ VendorBillPaid');
    }

    private function fireLowStockDetected(): void
    {
        $product = Product::first();
        $warehouse = Warehouse::first();
        if (! $product || ! $warehouse) {
            $this->warn('  · skipped LowStockDetected (no Product or Warehouse row)');
            return;
        }
        LowStockDetected::dispatch($product, $warehouse, 1, 10);
        $this->line('  ✓ LowStockDetected');
    }
}
