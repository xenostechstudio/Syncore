<?php

namespace App\Livewire\HR\Attendance;

use App\Exports\AttendancesExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Attendance')]
class Index extends Component
{
    use WithIndexComponent;

    #[Url]
    public string $employeeId = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedEmployeeId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'groupBy', 'employeeId']);
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
        $this->clearSelection();
    }

    protected function getCustomActiveFilterCount(): int
    {
        $count = 0;
        if ($this->employeeId !== '') {
            $count++;
        }
        if ($this->dateFrom !== '' && $this->dateFrom !== now()->startOfMonth()->format('Y-m-d')) {
            $count++;
        }
        if ($this->dateTo !== '' && $this->dateTo !== now()->endOfMonth()->format('Y-m-d')) {
            $count++;
        }

        return $count;
    }

    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $attendances = Attendance::whereIn('id', $this->selected)->with('employee')->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($attendances as $attendance) {
            $label = ($attendance->employee?->name ?? '—') . ' - ' . $attendance->date?->format('M d');
            if ($attendance->is_manual || $attendance->status === 'absent') {
                $canDelete[] = ['id' => $attendance->id, 'name' => $label];
            } else {
                $cannotDelete[] = [
                    'id' => $attendance->id,
                    'name' => $label,
                    'reason' => 'Has check-in data — only manual/absent records can be deleted',
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

        $count = Attendance::whereIn('id', $this->selected)
            ->where(fn ($q) => $q->where('is_manual', true)->orWhere('status', 'absent'))
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} attendance records deleted.");
    }

    public function exportSelected()
    {
        return Excel::download(new AttendancesExport($this->getQuery()), 'attendances-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function getStatisticsProperty(): array
    {
        $base = Attendance::whereBetween('date', [$this->dateFrom, $this->dateTo]);

        return [
            'total' => (clone $base)->count(),
            'present' => (clone $base)->where('status', 'present')->count(),
            'late' => (clone $base)->where('status', 'late')->count(),
            'absent' => (clone $base)->where('status', 'absent')->count(),
            'half_day' => (clone $base)->where('status', 'half_day')->count(),
        ];
    }

    protected function getQuery()
    {
        return Attendance::query()
            ->with(['employee', 'workSchedule'])
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->search, fn ($q) => $q->whereHas('employee', fn ($eq) => $eq
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->employeeId, fn ($q) => $q->where('employee_id', $this->employeeId))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));
    }

    protected function getModelClass(): string
    {
        return Attendance::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->orderBy('date', 'asc'),
            'late_high' => $this->getQuery()->orderBy('late_minutes', 'desc'),
            'duration_high' => $this->getQuery()->orderBy('work_duration_minutes', 'desc'),
            default => $this->getQuery()->orderBy('date', 'desc'),
        };

        return view('livewire.hr.attendance.index', [
            'attendances' => $query->paginate(15, ['*'], 'page', $this->page),
            'employees' => Employee::where('status', 'active')->orderBy('name')->get(),
            'statistics' => $this->statistics,
        ]);
    }
}
