<?php

namespace App\Listeners\HR;

use App\Events\LeaveRequestApproved;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLeaveApprovedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(LeaveRequestApproved $event): void
    {
        $leaveRequest = $event->leaveRequest;
        $employee = $leaveRequest->employee;

        NotificationService::create(
            type: 'leave_approved',
            title: 'Leave Request Approved',
            message: "Leave request for {$employee->full_name} ({$leaveRequest->leaveType->name}) from {$leaveRequest->start_date->format('M d, Y')} to {$leaveRequest->end_date->format('M d, Y')} has been approved.",
            notifiable: $leaveRequest,
            userId: $employee->user_id,
            data: [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $employee->id,
                'leave_type' => $leaveRequest->leaveType->name,
                'start_date' => $leaveRequest->start_date->toDateString(),
                'end_date' => $leaveRequest->end_date->toDateString(),
                'days' => $leaveRequest->days,
            ]
        );
    }
}
