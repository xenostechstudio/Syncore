<?php

namespace App\Livewire\HR;

use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\HR\LeaveRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('HR')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.hr.index', [
            'totalEmployees' => Employee::where('status', 'active')->count(),
            'totalDepartments' => Department::where('is_active', true)->count(),
            'pendingLeaveRequests' => LeaveRequest::where('status', 'pending')->count(),
            'recentEmployees' => Employee::with(['department', 'position'])
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}
