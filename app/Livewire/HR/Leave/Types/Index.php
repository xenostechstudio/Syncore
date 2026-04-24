<?php

namespace App\Livewire\HR\Leave\Types;

use App\Livewire\Concerns\WithIndexComponent;
use App\Models\HR\LeaveType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Leave Types')]
class Index extends Component
{
    use WithIndexComponent;

    public function mount(): void
    {
        $this->sort = 'name_asc';
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $leaveTypes = LeaveType::whereIn('id', $this->selected)
            ->withCount('leaveRequests')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($leaveTypes as $type) {
            if ($type->leave_requests_count === 0) {
                $canDelete[] = ['id' => $type->id, 'name' => $type->name];
            } else {
                $cannotDelete[] = [
                    'id' => $type->id,
                    'name' => $type->name,
                    'reason' => "Has {$type->leave_requests_count} leave requests",
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

        $typesWithRequests = LeaveType::whereIn('id', $this->selected)
            ->whereHas('leaveRequests')
            ->pluck('id')
            ->toArray();

        $deletableIds = array_diff($this->selected, array_map('strval', $typesWithRequests));

        if (empty($deletableIds)) {
            session()->flash('error', 'No leave types can be deleted. All have leave requests.');
            $this->cancelDelete();
            return;
        }

        $count = LeaveType::whereIn('id', $deletableIds)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} leave type(s) deleted.");
    }

    protected function getQuery()
    {
        return LeaveType::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn ($q) => $q->where('is_active', false));
    }

    protected function getModelClass(): string
    {
        return LeaveType::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'name_desc' => $this->getQuery()->orderBy('name', 'desc'),
            'days_high' => $this->getQuery()->orderBy('days_per_year', 'desc'),
            'days_low' => $this->getQuery()->orderBy('days_per_year', 'asc'),
            default => $this->getQuery()->orderBy('name', 'asc'),
        };

        return view('livewire.hr.leave.types.index', [
            'leaveTypes' => $query->paginate(15, ['*'], 'page', $this->page),
        ]);
    }
}
