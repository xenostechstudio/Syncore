<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'Settings'])]
#[Title('General Setup')]
class Index extends Component
{
    public function render()
    {
        $totalRoles = 0;
        if (class_exists('\Spatie\Permission\Models\Role')) {
            $totalRoles = \Spatie\Permission\Models\Role::count();
        }

        return view('livewire.settings.index', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('email_verified_at', '!=', null)->count(),
            'totalRoles' => $totalRoles,
            'recentUsers' => User::latest()->take(5)->get(),
        ]);
    }
}
