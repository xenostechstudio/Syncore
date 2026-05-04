<?php

namespace App\Livewire\Settings\Users;

use App\Exports\UsersExport;
use App\Livewire\Concerns\WithIndexComponent;
use App\Livewire\Concerns\WithPermissions;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('Users')]
class Index extends Component
{
    use WithIndexComponent, WithPermissions;

    public array $visibleColumns = [
        'user' => true,
        'status' => true,
        'joined' => true,
    ];

    public function toggleColumn(string $column): void
    {
        if (isset($this->visibleColumns[$column])) {
            $this->visibleColumns[$column] = ! $this->visibleColumns[$column];
        }
    }

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
                $canDelete[] = ['id' => $user->id, 'name' => $user->name];
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
        $this->authorizePermission('users.delete');

        if (empty($this->selected)) {
            return;
        }

        $currentUserId = auth()->id();
        $idsToDelete = array_filter($this->selected, fn ($id) => (int) $id !== $currentUserId);

        if (empty($idsToDelete)) {
            session()->flash('error', 'Cannot delete your own account.');
            $this->cancelDelete();
            return;
        }

        $count = User::whereIn('id', $idsToDelete)->delete();

        $this->cancelDelete();
        session()->flash('success', "{$count} users deleted successfully.");
    }

    public function exportSelected()
    {
        $filename = empty($this->selected)
            ? 'users-' . now()->format('Y-m-d') . '.xlsx'
            : 'users-selected-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new UsersExport($this->selected ?: null), $filename);
    }

    protected function getQuery()
    {
        return User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($sub) => $sub
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->when($this->status === 'active', fn ($q) => $q->whereNotNull('email_verified_at'))
            ->when($this->status === 'pending', fn ($q) => $q->whereNull('email_verified_at'));
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    public function render()
    {
        $query = match ($this->sort) {
            'oldest' => $this->getQuery()->orderBy('created_at', 'asc'),
            'name' => $this->getQuery()->orderBy('name', 'asc'),
            default => $this->getQuery()->orderBy('created_at', 'desc'),
        };

        return view('livewire.settings.users.index', [
            'users' => $query->paginate(12, ['*'], 'page', $this->page),
        ]);
    }
}
