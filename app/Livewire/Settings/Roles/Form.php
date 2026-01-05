<?php

namespace App\Livewire\Settings\Roles;

use App\Livewire\Concerns\WithNotes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.settings')]
#[Title('Role')]
class Form extends Component
{
    use WithNotes;
    #[Locked]
    public ?int $roleId = null;
    public string $roleName = '';
    public string $roleGuard = 'web';
    public array $selectedPermissions = [];
    public array $activityLog = [];
    public string $noteDraft = '';

    public array $moduleGroups = [
        'supply_chain' => [
            'label' => 'Supply Chain',
            'modules' => [
                ['key' => 'purchase', 'label' => 'Purchase'],
                ['key' => 'inventory', 'label' => 'Inventory'],
                ['key' => 'delivery', 'label' => 'Delivery'],
            ],
        ],
        'sales' => [
            'label' => 'Sales',
            'modules' => [
                ['key' => 'sales', 'label' => 'Sales Order'],
                ['key' => 'invoicing', 'label' => 'Invoicing'],
            ],
        ],
        'other' => [
            'label' => 'Other',
            'modules' => [
                ['key' => 'settings', 'label' => 'General Setup'],
            ],
        ],
    ];

    public array $moduleAccessLevels = [];

    public array $accessLevelOptions = [
        '' => 'No Access',
        'view' => 'View Only',
        'full' => 'Full Access',
    ];

    public function mount(?int $id = null): void
    {
        if (! class_exists('\Spatie\Permission\Models\Role')) {
            session()->flash('error', 'Spatie Permission package is not installed.');
            $this->redirect(route('settings.roles.index'), navigate: true);
            return;
        }

        // Initialize module access levels
        foreach ($this->moduleGroups as $group) {
            foreach ($group['modules'] as $module) {
                $this->moduleAccessLevels[$module['key']] = '';
            }
        }

        $role = null;

        if ($id) {
            $role = \Spatie\Permission\Models\Role::with('permissions')->findOrFail($id);
            $this->roleId = $role->id;
            $this->roleName = $role->name;
            $this->roleGuard = $role->guard_name;
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

            // Determine access levels from permissions
            foreach ($this->moduleGroups as $group) {
                foreach ($group['modules'] as $module) {
                    $key = $module['key'];
                    if (in_array("{$key}.full", $this->selectedPermissions, true)) {
                        $this->moduleAccessLevels[$key] = 'full';
                    } elseif (in_array("{$key}.view", $this->selectedPermissions, true)) {
                        $this->moduleAccessLevels[$key] = 'view';
                    } else {
                        $this->moduleAccessLevels[$key] = '';
                    }
                }
            }
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

        // Build permissions from module access levels
        $permissionsToSync = [];
        
        foreach ($this->moduleAccessLevels as $module => $level) {
            if ($level !== '') {
                // Add module access permission
                $permissionsToSync[] = "access.{$module}";
                
                // Add level-based permissions (full includes view)
                $permissionsToSync[] = "{$module}.view";
                if ($level === 'full') {
                    $permissionsToSync[] = "{$module}.full";
                }
            }
        }

        // Get all module keys for filtering
        $moduleKeys = [];
        foreach ($this->moduleGroups as $group) {
            foreach ($group['modules'] as $module) {
                $moduleKeys[] = $module['key'];
            }
        }

        // Merge with other selected permissions (from fine-grained tab)
        $otherPermissions = array_filter($this->selectedPermissions, function ($perm) use ($moduleKeys) {
            foreach ($moduleKeys as $key) {
                if (str_starts_with($perm, "access.{$key}") || str_starts_with($perm, "{$key}.")) {
                    return false;
                }
            }
            return true;
        });

        $allPermissions = array_unique(array_merge($permissionsToSync, $otherPermissions));
        $role->syncPermissions($allPermissions);
        
        $this->selectedPermissions = $allPermissions;
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

    public function setModuleAccessLevel(string $module, string $level): void
    {
        $this->moduleAccessLevels[$module] = $level;
    }

    public function selectAllModuleAccess(): void
    {
        foreach ($this->moduleGroups as $group) {
            foreach ($group['modules'] as $module) {
                $this->moduleAccessLevels[$module['key']] = 'full';
            }
        }
    }

    public function deselectAll(): void
    {
        foreach ($this->moduleGroups as $group) {
            foreach ($group['modules'] as $module) {
                $this->moduleAccessLevels[$module['key']] = '';
            }
        }
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
        
        // Get all module keys for filtering
        $moduleKeys = [];
        foreach ($this->moduleGroups as $group) {
            foreach ($group['modules'] as $module) {
                $moduleKeys[] = $module['key'];
            }
        }

        $otherPermissionGroups = $permissions
            ->reject(function ($permission) use ($moduleKeys) {
                foreach ($moduleKeys as $key) {
                    if (str_starts_with($permission->name, "access.{$key}") || 
                        str_starts_with($permission->name, "{$key}.")) {
                        return true;
                    }
                }
                return false;
            })
            ->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[0] ?? 'general';
            });

        return view('livewire.settings.roles.form', [
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
