<?php

namespace App\Livewire\HR\Attendance;

use App\Exports\AttendancesExport;
use App\Livewire\Concerns\WithManualPagination;
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
    use WithManualPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $employeeId = '';

    #[Url]
    public string $sort = 'latest';

    #[Url]
    public string $view = 'list';

    public string $dateFrom = '';
    public string $dateTo = '';

    public array $selected = [];
    public bool $selectAll = false;

    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedStatus(): void
    {
        $this->page = 1;
    }

    public function updatedEmployeeId(): void
    {
        $this->page = 1;
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

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort', 'employeeId']);
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
        $this->page = 1;
        $this->clearSelection();
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
            if ($attendance->is_manual || $attendance->status === 'absent') {
                $canDelete[] = [
                    'id' => $attendance->id,
                    'name' => $attendance->employee?->name . ' - ' . $attendance->date?->format('M d'),
                ];
            } else {
                $cannotDelete[] = [
                    'id' => $attendance->id,
                    'name' => $attendance->employee?->name . ' - ' . $attendance->date?->format('M d'),
                    'reason' => "Has check-in data — only manual/absent records can be deleted",
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
            ->where(fn($q) => $q->where('is_manual', true)->orWhere('status', 'absent'))
            ->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} attendance records deleted.");
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    public function exportSelected()
    {
        $ids = empty($this->selected) ? null : $this->selected;
        return Excel::download(new AttendancesExport($this->getQuery()), 'attendances-' . now()->format('Y-m-d') . '.xlsx');
    }

    protected function getQuery()
    {
        return Attendance::query()
            ->with(['employee', 'workSchedule'])
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($q) =>
                $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('email', 'ilike', "%{$this->search}%")
            ))
            ->when($this->employeeId, fn($q) => $q->where('employee_id', $this->employeeId))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->sort === 'latest', fn($q) => $q->orderBy('date', 'desc'))
            ->when($this->sort === 'oldest', fn($q) => $q->orderBy('date', 'asc'))
            ->when($this->sort === 'late_high', fn($q) => $q->orderBy('late_minutes', 'desc'))
            ->when($this->sort === 'duration_high', fn($q) => $q->orderBy('work_duration_minutes', 'desc'));
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

    public bool $showStats = false;

    public function toggleStats(): void
    {
        $this->showStats = !$this->showStats;
    }

    public function render()
    {
        $attendances = $this->getQuery()->paginate(15, ['*'], 'page', $this->page);

        $employees = Employee::where('status', 'active')->orderBy('name')->get();

        return view('livewire.hr.attendance.index', [
            'attendances' => $attendances,
            'employees' => $employees,
            'statistics' => $this->statistics,
        ]);
    }
}
