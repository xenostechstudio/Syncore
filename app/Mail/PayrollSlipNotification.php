<?php

namespace App\Mail;

use App\Models\HR\PayrollItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayrollSlipNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PayrollItem $payrollItem
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payroll Slip - ' . $this->payrollItem->period?->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payroll-slip-notification',
            with: [
                'payrollItem' => $this->payrollItem,
                'employee' => $this->payrollItem->employee,
                'period' => $this->payrollItem->period,
            ],
        );
    }
}
