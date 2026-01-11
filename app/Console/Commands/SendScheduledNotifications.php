<?php

namespace App\Console\Commands;

use App\Models\Inventory\Product;
use App\Models\Invoicing\Invoice;
use App\Models\Purchase\VendorBill;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';
    protected $description = 'Send scheduled notifications for overdue invoices, low stock, etc.';

    public function handle(): int
    {
        $this->info('Sending scheduled notifications...');

        $this->checkOverdueInvoices();
        $this->checkLowStock();
        $this->checkOverdueBills();
        $this->checkBillsDueSoon();

        $this->info('Scheduled notifications sent successfully.');

        return Command::SUCCESS;
    }

    protected function checkOverdueInvoices(): void
    {
        $overdueInvoices = Invoice::whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->get();

        foreach ($overdueInvoices as $invoice) {
            // Update status to overdue
            $invoice->update(['status' => 'overdue']);

            // Notify relevant users (e.g., sales team, admin)
            $userIds = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'sales', 'accountant']);
            })->pluck('id')->toArray();

            if (!empty($userIds)) {
                NotificationService::invoiceOverdue($invoice, $userIds);
            }
        }

        $this->info("Checked {$overdueInvoices->count()} overdue invoices.");
    }

    protected function checkLowStock(): void
    {
        $lowStockProducts = Product::where('status', 'active')
            ->whereColumn('quantity', '<=', 'reorder_point')
            ->where('quantity', '>', 0)
            ->get();

        $outOfStockProducts = Product::where('status', 'active')
            ->where('quantity', '<=', 0)
            ->get();

        $userIds = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'inventory', 'warehouse']);
        })->pluck('id')->toArray();

        if (!empty($userIds)) {
            foreach ($lowStockProducts as $product) {
                NotificationService::lowStock($product, $userIds);
            }

            foreach ($outOfStockProducts as $product) {
                NotificationService::outOfStock($product, $userIds);
            }
        }

        $this->info("Checked {$lowStockProducts->count()} low stock and {$outOfStockProducts->count()} out of stock products.");
    }

    protected function checkOverdueBills(): void
    {
        $overdueBills = VendorBill::whereIn('status', ['pending', 'partial'])
            ->where('due_date', '<', now())
            ->get();

        foreach ($overdueBills as $bill) {
            $bill->update(['status' => 'overdue']);

            $userIds = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'purchase', 'accountant']);
            })->pluck('id')->toArray();

            if (!empty($userIds)) {
                NotificationService::billOverdue($bill, $userIds);
            }
        }

        $this->info("Checked {$overdueBills->count()} overdue bills.");
    }

    protected function checkBillsDueSoon(): void
    {
        // Bills due in 3 days
        $billsDueSoon = VendorBill::whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [now(), now()->addDays(3)])
            ->get();

        $userIds = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'purchase', 'accountant']);
        })->pluck('id')->toArray();

        if (!empty($userIds)) {
            foreach ($billsDueSoon as $bill) {
                $daysUntilDue = now()->diffInDays($bill->due_date);
                NotificationService::billDueSoon($bill, $daysUntilDue, $userIds);
            }
        }

        $this->info("Notified about {$billsDueSoon->count()} bills due soon.");
    }
}
