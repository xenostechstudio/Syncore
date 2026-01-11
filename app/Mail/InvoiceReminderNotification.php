<?php

namespace App\Mail;

use App\Models\Invoicing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public int $daysOverdue = 0
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysOverdue > 0
            ? "Payment Reminder: Invoice #{$this->invoice->invoice_number} is {$this->daysOverdue} days overdue"
            : "Payment Reminder: Invoice #{$this->invoice->invoice_number}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice-reminder',
            with: [
                'invoice' => $this->invoice,
                'customer' => $this->invoice->customer,
                'daysOverdue' => $this->daysOverdue,
                'balanceDue' => $this->invoice->total - ($this->invoice->paid_amount ?? 0),
                'publicUrl' => $this->invoice->public_url ?? null,
            ],
        );
    }
}
