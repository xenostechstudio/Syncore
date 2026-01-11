<?php

namespace App\Mail;

use App\Models\HR\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $action = 'approved' // approved, rejected, pending
    ) {}

    public function envelope(): Envelope
    {
        $status = ucfirst($this->action);
        $employeeName = $this->leaveRequest->employee?->name ?? 'Employee';

        return new Envelope(
            subject: "Leave Request {$status}: {$employeeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.leave-approval',
            with: [
                'leaveRequest' => $this->leaveRequest,
                'employee' => $this->leaveRequest->employee,
                'leaveType' => $this->leaveRequest->leaveType,
                'action' => $this->action,
                'approver' => $this->leaveRequest->approver,
                'startDate' => $this->leaveRequest->start_date?->format('M d, Y'),
                'endDate' => $this->leaveRequest->end_date?->format('M d, Y'),
                'days' => $this->leaveRequest->days,
            ],
        );
    }
}
