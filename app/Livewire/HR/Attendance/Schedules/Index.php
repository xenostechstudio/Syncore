<?php

namespace App\Livewire\HR\Attendance\Schedules;

use App\Livewire\Concerns\WithIndexComponent;
use App\Models\HR\WorkSchedule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Work Schedules')]
class Index extends Component
{
    use WithIndexComponent;

    public function mount(): void
    {
        $this->sort = 'name_asc';
    }

    public function toggleStatus(int $id): void
    {
        $schedule = WorkSchedule::findOrFail($id);
        $schedule->update(['is_active' => ! $schedule->is_active]);
        session()->flash('success', __('common.updated_successfully'));
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $schedules = WorkSchedule::whereIn('id', $this->selected)
            ->withCount('employeeSchedules')
            ->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($schedules as $schedule) {
            if ($schedule->employee_schedules_count === 0) {
                $canDelete[] = ['id' => $schedule->id, 'name' => $schedule->name];
            } else {
                $cannotDelete[] = [
                    'id' => $schedule->id,
                    'name' => $schedule->name,
                    'reason' => __('attendance.schedule_in_use'),
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

        $count = WorkSchedule::whereIn('id', $this->selected)
            ->whereDoesntHave('employeeSchedules')
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} schedules deleted.");
    }

    protected function getQuery()
    {
        return WorkSchedule::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->status !== '', fn ($q) => $q->where('is_active', $this->status === 'active' || $this->status === '1'));
    }

    protected function getModelClass(): string
    {
        return WorkSchedule::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'name_desc' => $this->getQuery()->orderByDesc('name'),
            'latest' => $this->getQuery()->latest(),
            'oldest' => $this->getQuery()->oldest(),
            default => $this->getQuery()->orderBy('name'),
        };

        return view('livewire.hr.attendance.schedules.index', [
            'schedules' => $query->paginate(15, ['*'], 'page', $this->page),
        ]);
    }
}
