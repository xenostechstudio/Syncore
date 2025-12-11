<?php

namespace App\Livewire\Settings\Users;

use App\Livewire\Concerns\WithManualPagination;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
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

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = User::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
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

        $users = $query->paginate(12, ['*'], 'page', $this->page);

        return view('livewire.settings.users.index', [
            'users' => $users,
        ]);
    }
}
