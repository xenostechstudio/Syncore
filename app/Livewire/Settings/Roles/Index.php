<?php

namespace App\Livewire\Settings\Roles;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('Roles & Permissions')]
class Index extends Component
{
    public function render()
    {
        $roles = [];
        if (class_exists('\Spatie\Permission\Models\Role')) {
            $roles = \Spatie\Permission\Models\Role::withCount('permissions')->get();
        }

        return view('livewire.settings.roles.index', [
            'roles' => $roles,
        ]);
    }
}
