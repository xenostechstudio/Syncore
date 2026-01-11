<?php

namespace App\Livewire\Settings\Users;

use App\Exports\UsersExport;
use App\Livewire\Concerns\WithManualPagination;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.settings')]
#[Title('Users')]
class Index extends Component
{
    use WithManualPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $sort = 'latest';

    public string $view = 'list';

    public array $selected = [];
    public bool $selectAll = false;

    // Delete confirmation
    public bool $showDeleteConfirm = false;
    public array $deleteValidation = [];

    public array $visibleColumns = [
        'user' => true,
        'status' => true,
        'joined' => true,
    ];

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = !$this->visibleColumns[$column];
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->selected = [];
        $this->selectAll = false;
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getUsersQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'status', 'sort']);
        $this->resetPage();
        $this->clearSelection();
    }

    // Bulk Actions
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $currentUserId = auth()->id();
        $users = User::whereIn('id', $this->selected)->get();

        $canDelete = [];
        $cannotDelete = [];

        foreach ($users as $user) {
            if ($user->id === $currentUserId) {
                $cannotDelete[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'reason' => 'Cannot delete your own account',
                ];
            } else {
                $canDelete[] = [
                    'id' => $user->id,
                    'name' => $user->name,
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

        // Don't allow deleting current user
        $currentUserId = auth()->id();
        $idsToDelete = array_filter($this->selected, fn($id) => (int) $id !== $currentUserId);

        if (empty($idsToDelete)) {
            session()->flash('error', 'Cannot delete your own account.');
            $this->cancelDelete();
            return;
        }

        $count = User::whereIn('id', $idsToDelete)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} users deleted successfully.");
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
            return Excel::download(new UsersExport(), 'users-' . now()->format('Y-m-d') . '.xlsx');
        }

        return Excel::download(new UsersExport($this->selected), 'users-selected-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function getUsersQuery()
    {
        $query = User::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', "%{$this->search}%")
                    ->orWhere('email', 'ilike', "%{$this->search}%");
            });
        }

        if ($this->status === 'active') {
            $query->whereNotNull('email_verified_at');
        } elseif ($this->status === 'pending') {
            $query->whereNull('email_verified_at');
        }

        if ($this->sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($this->sort === 'name') {
            $query->orderBy('name', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function render()
    {
        $users = $this->getUsersQuery()->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.settings.users.index', [
            'users' => $users,
        ]);
    }
}
