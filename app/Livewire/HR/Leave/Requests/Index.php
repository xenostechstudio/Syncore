<?php

namespace App\Livewire\HR\Leave\Requests;

use App\Exports\LeaveRequestsExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Livewire\Concerns\WithPermissions;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Leave Requests')]
class Index extends Component
{
    use WithIndexComponent, WithPermissions;

    #[Url]
    public string $leaveTypeId = '';

    public function updatedLeaveTypeId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy', 'leaveTypeId']);
        $this->resetPage();
        $this->clearSelection();
    }

    protected function getCustomActiveFilterCount(): int
    {
        return $this->leaveTypeId !== '' ? 1 : 0;
    }

    public function approveSelected(): void
    {
        $this->authorizePermission('leave.approve');

        if (empty($this->selected)) {
            return;
        }

        $requests = LeaveRequest::whereIn('id', $this->selected)
            ->with(['leaveType', 'employee'])
            ->where('status', 'pending')
            ->get();
        foreach ($requests as $request) {
            $request->approve(auth()->id());
        }
        session()->flash('success', count($requests) . ' leave request(s) approved.');
        $this->clearSelection();
    }

    public function rejectSelected(): void
    {
        $this->authorizePermission('leave.reject');

        if (empty($this->selected)) {
            return;
        }

        $requests = LeaveRequest::whereIn('id', $this->selected)
            ->with(['leaveType', 'employee'])
            ->where('status', 'pending')
            ->get();
        foreach ($requests as $request) {
            $request->reject(auth()->id());
        }
        session()->flash('success', count($requests) . ' leave request(s) rejected.');
        $this->clearSelection();
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $requests = LeaveRequest::whereIn('id', $this->selected)->with('employee')->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($requests as $request) {
            $statusValue = $request->status?->value ?? $request->status;
            $label = ($request->employee?->name ?? '—') . ' - ' . $request->start_date?->format('M d');

            if (in_array($statusValue, ['pending', 'rejected', 'cancelled', 'draft'], true)) {
                $canDelete[] = ['id' => $request->id, 'name' => $label];
            } else {
                $cannotDelete[] = [
                    'id' => $request->id,
                    'name' => $label,
                    'reason' => "Status is '{$statusValue}' - approved requests cannot be deleted",
                ];
            }
        }

        $this->deleteValidation = [
            'canDelete' => $canDelete,
            'cannotDelete' => $cannotDelete,
            'totalSelected' => count($this->selected),
        ];

        $this->showDeleteConfirm = true;
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $count = LeaveRequest::whereIn('id', $this->selected)
            ->whereIn('status', ['pending', 'rejected', 'cancelled', 'draft'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} leave request(s) deleted.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'leave-requests-' . now()->format('Y-m-d') . '.xlsx'
            : 'leave-requests-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new LeaveRequestsExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return LeaveRequest::query()
            ->with(['employee.position', 'leaveType', 'approver'])
            ->when($this->search, fn ($q) => $q->whereHas('employee', fn ($eq) => $eq
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->leaveTypeId, fn ($q) => $q->where('leave_type_id', $this->leaveTypeId));
    }

    protected function getModelClass(): string
    {
        return LeaveRequest::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->orderBy('created_at', 'asc'),
            'days_high' => $this->getQuery()->orderBy('days', 'desc'),
            'days_low' => $this->getQuery()->orderBy('days', 'asc'),
            default => $this->getQuery()->orderBy('created_at', 'desc'),
        };

        return view('livewire.hr.leave.requests.index', [
            'requests' => $query->paginate(15, ['*'], 'page', $this->page),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
