<x-mail::message>
# Leave Request {{ ucfirst($action) }}

Dear {{ $employee?->name ?? 'Employee' }},

@if($action === 'approved')
Your leave request has been **approved**.
@elseif($action === 'rejected')
Your leave request has been **rejected**.
@else
Your leave request is **pending approval**.
@endif

**Leave Request Details:**
- Leave Type: {{ $leaveType?->name ?? '-' }}
- Start Date: {{ $startDate }}
- End Date: {{ $endDate }}
- Duration: {{ $days }} day(s)
@if($leaveRequest->reason)
- Reason: {{ $leaveRequest->reason }}
@endif

@if($action === 'approved' && $approver)
Approved by: {{ $approver->name }}
@elseif($action === 'rejected' && $approver)
Rejected by: {{ $approver->name }}
@endif

@if($action === 'rejected' && $leaveRequest->rejection_reason)
**Rejection Reason:** {{ $leaveRequest->rejection_reason }}
@endif

<x-mail::button :url="route('hr.leave.requests.index')">
View Leave Requests
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
