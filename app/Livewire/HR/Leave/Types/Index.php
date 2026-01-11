<?php

namespace App\Livewire\HR\Leave\Types;

use App\Models\HR\LeaveType;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Leave Types')]
class Index extends Component
{
    use WithPagination;

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
                $canDelete[] = [
                    'id' => $type->id,
                    'name' => $type->name,
                ];
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

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->deleteValidation = [];
        $this->clearSelection();
    }

    protected function getQuery()
    {
        return LeaveType::query()
            ->when($this->search, fn($q) => $q->where('name', 'ilike', "%{$this->search}%")
                ->orWhere('code', 'ilike', "%{$this->search}%"))
            ->when($this->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($this->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->when($this->sort === 'name_asc', fn($q) => $q->orderBy('name', 'asc'))
            ->when($this->sort === 'name_desc', fn($q) => $q->orderBy('name', 'desc'))
            ->when($this->sort === 'days_high', fn($q) => $q->orderBy('days_per_year', 'desc'))
            ->when($this->sort === 'days_low', fn($q) => $q->orderBy('days_per_year', 'asc'));
    }

    public function render()
    {
        $leaveTypes = $this->getQuery()->paginate(15);

        return view('livewire.hr.leave.types.index', [
            'leaveTypes' => $leaveTypes,
        ]);
    }
}
