<?php

namespace App\Livewire\HR\Attendance;

use App\Services\AttendanceService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Check In')]
class CheckIn extends Component
{
    public string $notes = '';
    public string $location = '';
    public bool $showModal = false;
    public string $action = '';

    protected AttendanceService $attendanceService;

    public function boot(AttendanceService $attendanceService): void
    {
        $this->attendanceService = $attendanceService;
    }

    public function openCheckInModal(): void
    {
        if (!$this->canCheckIn()) {
            session()->flash('error', __('attendance.already_checked_in'));
            return;
        }

        $this->action = 'check_in';
        $this->showModal = true;
        $this->reset(['notes', 'location']);
    }

    public function openCheckOutModal(): void
    {
        if (!$this->canCheckOut()) {
            session()->flash('error', __('attendance.not_checked_in'));
            return;
        }

        $this->action = 'check_out';
        $this->showModal = true;
        $this->reset(['notes', 'location']);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['notes', 'location', 'action']);
    }

    public function submit(): void
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            session()->flash('error', __('attendance.employee_not_found'));
            return;
        }

        $data = [
            'notes' => $this->notes,
            'location' => $this->location,
            'device' => 'web',
        ];

        try {
            if ($this->action === 'check_in') {
                $this->attendanceService->checkIn($employee, $data);
                session()->flash('success', __('attendance.checked_in_successfully'));
            } else {
                $this->attendanceService->checkOut($employee, $data);
                session()->flash('success', __('attendance.checked_out_successfully'));
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function canCheckIn(): bool
    {
        $employee = auth()->user()->employee;
        return $employee && $this->attendanceService->canCheckIn($employee);
    }

    public function canCheckOut(): bool
    {
        $employee = auth()->user()->employee;
        return $employee && $this->attendanceService->canCheckOut($employee);
    }

    public function render()
    {
        $employee = auth()->user()->employee;
        $todayAttendance = $employee ? $this->attendanceService->getTodayAttendance($employee) : null;

        return view('livewire.hr.attendance.check-in', [
            'todayAttendance' => $todayAttendance,
            'canCheckIn' => $this->canCheckIn(),
            'canCheckOut' => $this->canCheckOut(),
        ]);
    }
}
