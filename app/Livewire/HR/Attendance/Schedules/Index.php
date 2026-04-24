<?php

namespace App\Livewire\HR\Attendance\Schedules;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\HR\WorkSchedule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Work Schedules')]
class Index extends Component
{
    use WithManualPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'name_asc';

    #[Url]
    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function updatedSearch(): void
    {
        $this->page = 1;
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function toggleStatus(int $id): void
    {
        $schedule = WorkSchedule::findOrFail($id);
        $schedule->update(['is_active' => !$schedule->is_active]);
        session()->flash('success', __('common.updated_successfully'));
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $schedules = WorkSchedule::whereIn('id', $this->selected)->withCount('employeeSchedules')->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($schedules as $schedule) {
            if ($schedule->employee_schedules_count === 0) {
                $canDelete[] = [
                    'id' => $schedule->id,
                    'name' => $schedule->name,
                ];
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

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    protected function getQuery()
    {
        return WorkSchedule::query()
            ->when($this->search, fn($q) => $q->where(fn($qq) =>
                $qq->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('code', 'ilike', "%{$this->search}%")
            ))
            ->when($this->status !== '', fn($q) => $q->where('is_active', $this->status))
            ->when($this->sort === 'name_asc', fn($q) => $q->orderBy('name'))
            ->when($this->sort === 'name_desc', fn($q) => $q->orderByDesc('name'))
            ->when($this->sort === 'latest', fn($q) => $q->latest())
            ->when($this->sort === 'oldest', fn($q) => $q->oldest());
    }

    public function render()
    {
        $schedules = $this->getQuery()->paginate(15, ['*'], 'page', $this->page);

        return view('livewire.hr.attendance.schedules.index', [
            'schedules' => $schedules,
        ]);
    }
}
