<?php

namespace App\Livewire\Settings\Roles;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.settings')]
#[Title('Roles & Permissions')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    public array $selected = [];
    public bool $selectAll = false;

    public bool $showDeleteModal = false;

    protected $queryString = ['search'];

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selected = $this->getRoles()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    public function confirmDelete(?int $roleId = null): void
    {
        if ($roleId) {
            $this->selected = [(string) $roleId];
        }
        $this->showDeleteModal = true;
    }

    public function deleteRoles(): void
    {
        if (!class_exists('\Spatie\Permission\Models\Role')) {
            return;
        }

        \Spatie\Permission\Models\Role::whereIn('id', $this->selected)->delete();
        
        session()->flash('success', count($this->selected) . ' role(s) deleted successfully.');
        $this->selected = [];
        $this->showDeleteModal = false;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
    }

    public function goToEdit(int $roleId): void
    {
        $this->redirect(route('settings.roles.edit', $roleId), navigate: true);
    }

    protected function getRoles()
    {
        if (!class_exists('\Spatie\Permission\Models\Role')) {
            return collect();
        }

        $query = \Spatie\Permission\Models\Role::withCount('permissions');

        if ($this->search) {
            $query->where('name', 'ilike', '%' . $this->search . '%');
        }

        return $query->orderBy('name')->paginate(10);
    }

    protected function getPermissions()
    {
        if (!class_exists('\Spatie\Permission\Models\Permission')) {
            return collect();
        }

        return \Spatie\Permission\Models\Permission::orderBy('name')->get();
    }

    protected function getGroupedPermissions()
    {
        $permissions = $this->getPermissions();
        
        return $permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'general';
        });
    }

    public function render()
    {
        return view('livewire.settings.roles.index', [
            'roles' => $this->getRoles(),
            'permissions' => $this->getPermissions(),
            'groupedPermissions' => $this->getGroupedPermissions(),
        ]);
    }
}
