<?php

namespace App\Policies;

use App\Models\HR\LeaveRequest;
use App\Models\User;
use App\Policies\Concerns\HandlesDocumentAuthorization;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeaveRequestPolicy
{
    use HandlesAuthorization, HandlesDocumentAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view leave requests');
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        // Users can view their own leave requests
        if ($leaveRequest->employee?->user_id === $user->id) {
            return true;
        }

        return $user->can('view leave requests');
    }

    public function create(User $user): bool
    {
        return $user->can('create leave requests');
    }

    public function update(User $user, LeaveRequest $leaveRequest): bool
    {
        // Owner can edit their own draft/pending requests
        if ($leaveRequest->employee?->user_id === $user->id) {
            return $leaveRequest->state->canEdit();
        }

        if (!$user->can('edit leave requests')) {
            return false;
        }

        return $leaveRequest->state->canEdit();
    }

    public function delete(User $user, LeaveRequest $leaveRequest): bool
    {
        // Owner can delete their own draft requests
        if ($leaveRequest->employee?->user_id === $user->id) {
            return $leaveRequest->status === 'draft';
        }

        if (!$user->can('delete leave requests')) {
            return false;
        }

        return $leaveRequest->status === 'draft';
    }

    public function submit(User $user, LeaveRequest $leaveRequest): bool
    {
        // Only owner can submit
        if ($leaveRequest->employee?->user_id !== $user->id) {
            return false;
        }

        return $leaveRequest->state->canSubmit();
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        if (!$user->can('approve leave requests')) {
            return false;
        }

        // Cannot approve own request
        if ($leaveRequest->employee?->user_id === $user->id) {
            return false;
        }

        return $leaveRequest->state->canApprove();
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        if (!$user->can('reject leave requests')) {
            return false;
        }

        // Cannot reject own request
        if ($leaveRequest->employee?->user_id === $user->id) {
            return false;
        }

        return $leaveRequest->state->canReject();
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        // Owner can cancel their own pending requests
        if ($leaveRequest->employee?->user_id === $user->id) {
            return $leaveRequest->state->canCancel();
        }

        if (!$user->can('cancel leave requests')) {
            return false;
        }

        return $leaveRequest->state->canCancel();
    }
}
