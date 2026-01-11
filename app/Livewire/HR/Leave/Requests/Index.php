<?php

namespace App\Livewire\HR\Leave\Requests;

use App\Exports\LeaveRequestsExport;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Leave Requests')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $leaveTypeId = '';

    #[Url]
    public string $view = 'list';

    #[Url]
    public string $sort = 'latest';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function goToPreviousPage(): void
    {
        $this->previousPage();
    }

    public function goToNextPage(): void
    {
        $this->nextPage();
    }

    public function approveSelected(): void
    {
        $requests = LeaveRequest::whereIn('id', $this->selected)->where('status', 'pending')->get();
        foreach ($requests as $request) {
            $request->approve(auth()->id());
        }
        session()->flash('success', count($requests) . ' leave request(s) approved.');
        $this->clearSelection();
    }

    public function rejectSelected(): void
    {
        $requests = LeaveRequest::whereIn('id', $this->selected)->where('status', 'pending')->get();
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

        $requests = LeaveRequest::whereIn('id', $this->selected)
            ->with('employee')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($requests as $request) {
            if (in_array($request->status, ['pending', 'rejected', 'cancelled'])) {
                $canDelete[] = [
                    'id' => $request->id,
                    'name' => $request->employee?->name . ' - ' . $request->start_date?->format('M d'),
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $request->id,
                    'name' => $request->employee?->name . ' - ' . $request->start_date?->format('M d'),
                    'reason' => "Status is '{$request->status}' - only pending/rejected/cancelled can be deleted",
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
            ->whereIn('status', ['pending', 'rejected', 'cancelled'])
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} leave request(s) deleted.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function exportSelected()
    {
        if (empty($this->selected)) {
            return Excel::download(new LeaveRequestsExport(), 'leave-requests-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new LeaveRequestsExport($this->selected), 'leave-requests-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getQuery()
    {
        return LeaveRequest::query()
            ->with(['employee', 'leaveType', 'approver'])
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($q) => 
                $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('email', 'ilike', "%{$this->search}%")
            ))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->leaveTypeId, fn($q) => $q->where('leave_type_id', $this->leaveTypeId))
            ->when($this->sort === 'latest', fn($q) => $q->orderBy('created_at', 'desc'))
            ->when($this->sort === 'oldest', fn($q) => $q->orderBy('created_at', 'asc'))
            ->when($this->sort === 'days_high', fn($q) => $q->orderBy('days', 'desc'))
            ->when($this->sort === 'days_low', fn($q) => $q->orderBy('days', 'asc'));
    }

    public function render()
    {
        $requests = $this->getQuery()->paginate(15);

        return view('livewire.hr.leave.requests.index', [
            'requests' => $requests,
            'leaveTypes' => LeaveType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
