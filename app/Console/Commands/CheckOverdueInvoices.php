<?php

namespace App\Console\Commands;

use App\Events\InvoiceOverdue;
use App\Models\Invoicing\Invoice;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for overdue invoices and update their status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for overdue invoices...');

        $overdueInvoices = Invoice::whereIn('status', ['sent', 'partial'])
            ->where('due_date', '<', now())
            ->get();

        $count = 0;

        foreach ($overdueInvoices as $invoice) {
            $oldStatus = $invoice->status;
            $invoice->update(['status' => 'overdue']);
            
            // Dispatch event for notification
            InvoiceOverdue::dispatch($invoice);
            
            $invoice->logStatusChange($oldStatus, 'overdue', 'Invoice marked as overdue by system');
            $count++;
        }

        $this->info("Updated {$count} invoices to overdue status.");

        return Command::SUCCESS;
    }
}
