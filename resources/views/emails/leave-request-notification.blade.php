<x-mail::message>
# Leave Request {{ ucfirst($action) }}

@if($action === 'submitted')
A new leave request has been submitted and requires your attention.
@elseif($action === 'approved')
Your leave request has been approved.
@elseif($action === 'rejected')
Your leave request has been rejected.
@endif

## Request Details

**Employee:** {{ $employee?->name ?? '-' }}  
**Leave Type:** {{ $leaveType?->name ?? '-' }}  
**Start Date:** {{ $leaveRequest->start_date?->format('M d, Y') }}  
**End Date:** {{ $leaveRequest->end_date?->format('M d, Y') }}  
**Duration:** {{ $leaveRequest->days }} day(s)  
**Status:** {{ ucfirst($leaveRequest->status) }}

@if($leaveRequest->reason)
**Reason:** {{ $leaveRequest->reason }}
@endif

@if($action === 'submitted')
<x-mail::button :url="route('hr.leave.requests.edit', $leaveRequest->id)">
Review Request
</x-mail::button>
@endif

@if($action === 'rejected' && $leaveRequest->rejection_reason)
**Rejection Reason:** {{ $leaveRequest->rejection_reason }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
