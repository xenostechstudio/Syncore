<?php

namespace App\Listeners\HR;

use App\Events\LeaveRequestRejected;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLeaveRejectedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LeaveRequestRejected $event): void
    {
        $leaveRequest = $event->leaveRequest;
        $employee = $leaveRequest->employee;

        $message = "Leave request for {$employee->full_name} ({$leaveRequest->leaveType->name}) from {$leaveRequest->start_date->format('M d, Y')} to {$leaveRequest->end_date->format('M d, Y')} has been rejected.";
        
        if ($event->reason) {
            $message .= " Reason: {$event->reason}";
        }

        NotificationService::create(
            type: 'leave_rejected',
            title: 'Leave Request Rejected',
            message: $message,
            notifiable: $leaveRequest,
            userId: $employee->user_id,
            data: [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $employee->id,
                'reason' => $event->reason,
            ]
        );
    }
}
