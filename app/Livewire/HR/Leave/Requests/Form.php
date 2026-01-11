<?php

namespace App\Livewire\HR\Leave\Requests;

use App\Enums\LeaveRequestState;
use App\Livewire\Concerns\WithNotes;
use App\Models\HR\Employee;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Leave Request')]
class Form extends Component
{
    use WithNotes;
    public ?int $requestId = null;
    public ?LeaveRequest $leaveRequest = null;

    public ?int $employeeId = null;
    public ?int $leaveTypeId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;
    public float $days = 1;
    public string $reason = '';
    public string $status = 'draft';

    #[Computed]
    public function selectedEmployee(): ?Employee
    {
        return $this->employeeId ? Employee::with(['position', 'department'])->find($this->employeeId) : null;
    }

    #[Computed]
    public function state(): LeaveRequestState
    {
        return LeaveRequestState::tryFrom($this->status) ?? LeaveRequestState::DRAFT;
    }

    protected function rules(): array
    {
        return [
            'employeeId' => 'required|exists:employees,id',
            'leaveTypeId' => 'required|exists:leave_types,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'days' => 'required|numeric|min:0.5',
            'reason' => 'nullable|string',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->requestId = $id;

        if ($id) {
            $this->leaveRequest = LeaveRequest::with(['employee', 'leaveType'])->findOrFail($id);
            $this->employeeId = $this->leaveRequest->employee_id;
            $this->leaveTypeId = $this->leaveRequest->leave_type_id;
            $this->startDate = $this->leaveRequest->start_date->format('Y-m-d');
            $this->endDate = $this->leaveRequest->end_date->format('Y-m-d');
            $this->days = $this->leaveRequest->days;
            $this->reason = $this->leaveRequest->reason ?? '';
            $this->status = $this->leaveRequest->status;
        } else {
            $this->startDate = now()->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');
            $this->status = LeaveRequestState::DRAFT->value;
        }
    }

    public function updatedStartDate(): void
    {
        $this->calculateDays();
    }

    public function updatedEndDate(): void
    {
        $this->calculateDays();
    }

    private function calculateDays(): void
    {
        if ($this->startDate && $this->endDate) {
            $start = \Carbon\Carbon::parse($this->startDate);
            $end = \Carbon\Carbon::parse($this->endDate);
            $this->days = max(1, $start->diffInDays($end) + 1);
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'employee_id' => $this->employeeId,
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'days' => $this->days,
            'reason' => $this->reason ?: null,
            'status' => $this->status,
        ];

        if ($this->requestId) {
            $this->leaveRequest->update($data);
            session()->flash('success', 'Leave request updated successfully.');
        } else {
            $data['status'] = LeaveRequestState::DRAFT->value;
            $this->leaveRequest = LeaveRequest::create($data);
            session()->flash('success', 'Leave request created successfully.');
            $this->redirect(route('hr.leave.requests.edit', $this->leaveRequest->id), navigate: true);
        }
    }

    public function submit(): void
    {
        if (!$this->leaveRequest) {
            $this->save();
        }

        if ($this->leaveRequest->submit()) {
            $this->status = LeaveRequestState::PENDING->value;
            session()->flash('success', 'Leave request submitted for approval.');
        } else {
            session()->flash('error', 'Cannot submit this leave request.');
        }
    }

    public function approve(): void
    {
        if (!$this->leaveRequest) return;

        if ($this->leaveRequest->approve(auth()->id())) {
            $this->status = LeaveRequestState::APPROVED->value;
            $this->leaveRequest->refresh();
            session()->flash('success', 'Leave request approved.');
        } else {
            session()->flash('error', 'Cannot approve this leave request.');
        }
    }

    public function reject(): void
    {
        if (!$this->leaveRequest) return;

        if ($this->leaveRequest->reject(auth()->id())) {
            $this->status = LeaveRequestState::REJECTED->value;
            $this->leaveRequest->refresh();
            session()->flash('success', 'Leave request rejected.');
        } else {
            session()->flash('error', 'Cannot reject this leave request.');
        }
    }

    public function cancel(): void
    {
        if (!$this->leaveRequest) return;

        if ($this->leaveRequest->cancel()) {
            $this->status = LeaveRequestState::CANCELLED->value;
            session()->flash('success', 'Leave request cancelled.');
        } else {
            session()->flash('error', 'Cannot cancel this leave request.');
        }
    }

    public function delete(): void
    {
        if (!$this->leaveRequest) return;

        $this->leaveRequest->delete();
        session()->flash('success', 'Leave request deleted successfully.');
        $this->redirect(route('hr.leave.requests.index'), navigate: true);
    }

    protected function getNotableModel()
    {
        return $this->leaveRequest;
    }

    public function getActivities()
    {
        if (!$this->requestId) {
            return collect();
        }

        $modelClass = LeaveRequest::class;

        // Get activity logs from custom activity_logs table
        $activities = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('activity_logs.model_type', $modelClass)
            ->where('activity_logs.model_id', $this->requestId)
            ->select('activity_logs.*', 'users.name as causer_name')
            ->orderByDesc('activity_logs.created_at')
            ->limit(20)
            ->get()
            ->map(fn($activity) => (object) [
                'id' => $activity->id,
                'type' => 'activity',
                'action' => $activity->action,
                'description' => $activity->description,
                'properties' => json_decode($activity->properties ?? '{}', true),
                'causer' => (object) ['name' => $activity->causer_name ?? $activity->user_name ?? 'System'],
                'created_at' => \Carbon\Carbon::parse($activity->created_at),
            ]);

        // Get notes
        $notes = $this->leaveRequest->notes()
            ->with('user')
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($note) {
                $note->type = 'note';
                $note->causer = $note->user;
                return $note;
            });

        // Merge and sort by created_at
        return $activities->concat($notes)
            ->sortByDesc('created_at')
            ->values()
            ->take(30);
    }

    public function render()
    {
        return view('livewire.hr.leave.requests.form', [
            'employees' => Employee::where('status', 'active')->orderBy('name')->get(),
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
            'activities' => $this->getActivities(),
        ]);
    }
}
