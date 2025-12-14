<?php

namespace App\Livewire\Settings\Roles;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('Role')]
class Form extends Component
{
    #[Locked]
    public ?int $roleId = null;
    public string $roleName = '';
    public string $roleGuard = 'web';
    public array $selectedPermissions = [];
    public array $activityLog = [];
    public string $noteDraft = '';

    public array $moduleCards = [
        [
            'permission' => 'access.sales',
            'label' => 'Sales',
            'description' => 'Quotations, orders, customers, and teams',
            'icon' => 'shopping-bag',
            'color' => 'violet',
        ],
        [
            'permission' => 'access.inventory',
            'label' => 'Inventory',
            'description' => 'Transfers, adjustments, products, warehouses',
            'icon' => 'cube',
            'color' => 'blue',
        ],
        [
            'permission' => 'access.purchase',
            'label' => 'Purchase',
            'description' => 'Suppliers, RFQs, purchase orders',
            'icon' => 'banknotes',
            'color' => 'amber',
        ],
        [
            'permission' => 'access.delivery',
            'label' => 'Delivery',
            'description' => 'Delivery orders and routes',
            'icon' => 'truck',
            'color' => 'cyan',
        ],
        [
            'permission' => 'access.invoicing',
            'label' => 'Invoicing',
            'description' => 'Invoices, payments, billing automation',
            'icon' => 'receipt-percent',
            'color' => 'emerald',
        ],
        [
            'permission' => 'access.settings',
            'label' => 'General Setup',
            'description' => 'Company profile, users, roles & localization',
            'icon' => 'cog-6-tooth',
            'color' => 'zinc',
        ],
    ];

    public function mount(?int $id = null): void
    {
        if (! class_exists('\Spatie\Permission\Models\Role')) {
            session()->flash('error', 'Spatie Permission package is not installed.');
            $this->redirect(route('settings.roles.index'), navigate: true);
            return;
        }

        $role = null;

        if ($id) {
            $role = \Spatie\Permission\Models\Role::with('permissions')->findOrFail($id);
            $this->roleId = $role->id;
            $this->roleName = $role->name;
            $this->roleGuard = $role->guard_name;
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        }

        $this->setActivityLog($role);
    }

    public function save(): void
    {
        $this->validate([
            'roleName' => 'required|string|max:255',
            'roleGuard' => 'required|string|max:50',
        ]);

        if (! class_exists('\Spatie\Permission\Models\Role')) {
            session()->flash('error', 'Spatie Permission package is not installed.');
            return;
        }

        $role = $this->roleId
            ? \Spatie\Permission\Models\Role::find($this->roleId)
            : null;

        if (! $role) {
            $role = new \Spatie\Permission\Models\Role();
        }

        $role->name = $this->roleName;
        $role->guard_name = $this->roleGuard;
        $role->save();
        $role->syncPermissions($this->selectedPermissions);

        $this->roleId = $role->id;
        $this->setActivityLog($role);

        session()->flash('success', 'Role saved successfully.');
        $this->redirect(route('settings.roles.edit', $role->id), navigate: true);
    }

    public function delete(): void
    {
        if (! $this->roleId || ! class_exists('\Spatie\Permission\Models\Role')) {
            return;
        }

        $role = \Spatie\Permission\Models\Role::find($this->roleId);
        if (! $role) {
            return;
        }

        $role->delete();

        session()->flash('success', 'Role deleted successfully.');
        $this->redirect(route('settings.roles.index'), navigate: true);
    }

    public function togglePermission(string $permission): void
    {
        if (in_array($permission, $this->selectedPermissions, true)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, [$permission]));
        } else {
            $this->selectedPermissions[] = $permission;
        }
    }

    public function addNote(): void
    {
        $note = trim($this->noteDraft);
        if ($note === '') {
            return;
        }

        array_unshift($this->activityLog, [
            'title' => 'Note added',
            'description' => $note,
            'time' => now()->format('M d, Y H:i'),
            'icon' => 'chat-bubble-oval-left-ellipsis',
            'color' => 'violet',
        ]);

        $this->noteDraft = '';
    }

    public function selectAllModuleAccess(): void
    {
        $modulePermissions = collect($this->moduleCards)->pluck('permission')->toArray();
        $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $modulePermissions)));
    }

    public function deselectAll(): void
    {
        $this->selectedPermissions = [];
    }

    protected function getPermissions(): Collection
    {
        if (! class_exists('\Spatie\Permission\Models\Permission')) {
            return collect();
        }

        return \Spatie\Permission\Models\Permission::orderBy('name')->get();
    }

    public function render()
    {
        $permissions = $this->getPermissions();
        $modulePermissionNames = collect($this->moduleCards)->pluck('permission');

        $otherPermissionGroups = $permissions
            ->reject(fn ($permission) => $modulePermissionNames->contains($permission->name))
            ->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[0] ?? 'general';
            });

        return view('livewire.settings.roles.form', [
            'modulePermissionNames' => $modulePermissionNames,
            'otherPermissionGroups' => $otherPermissionGroups,
            'totalPermissions' => $permissions->count(),
        ]);
    }

    private function setActivityLog(?\Spatie\Permission\Models\Role $role): void
    {
        if (! $role) {
            $this->activityLog = [[
                'title' => 'Draft role started',
                'description' => 'Define permissions before inviting teammates.',
                'time' => now()->format('M d, Y H:i'),
                'icon' => 'sparkles',
                'color' => 'violet',
            ]];

            return;
        }

        $log = [[
            'title' => 'Role saved',
            'description' => Str::headline($role->name) . ' updated.',
            'time' => $role->updated_at?->diffForHumans() ?? '',
            'icon' => 'document-check',
            'color' => 'emerald',
        ]];

        $log[] = [
            'title' => 'Role created',
            'description' => 'Created via settings module.',
            'time' => $role->created_at?->diffForHumans() ?? '',
            'icon' => 'sparkles',
            'color' => 'blue',
        ];
        $log[] = [
            'title' => 'Permissions synced',
            'description' => count($this->selectedPermissions) . ' permissions assigned.',
            'time' => now()->format('M d, Y H:i'),
            'icon' => 'shield-check',
            'color' => 'amber',
        ];

        $this->activityLog = $log;
    }
}
