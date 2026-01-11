<?php

namespace App\Mail;

use App\Models\HR\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $action = 'submitted' // submitted, approved, rejected
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->action) {
            'approved' => 'Leave Request Approved',
            'rejected' => 'Leave Request Rejected',
            default => 'New Leave Request Submitted',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave-request-notification',
            with: [
                'leaveRequest' => $this->leaveRequest,
                'employee' => $this->leaveRequest->employee,
                'leaveType' => $this->leaveRequest->leaveType,
                'action' => $this->action,
            ],
        );
    }
}
