<?php

namespace App\Livewire\HR\Attendance\Schedules;

use App\Models\HR\WorkSchedule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Work Schedule')]
class Form extends Component
{
    public ?int $scheduleId = null;
    public string $name = '';
    public string $code = '';
    public string $start_time = '';
    public string $end_time = '';
    public int $break_duration = 60;
    public array $work_days = [];
    public bool $is_flexible = false;
    public int $grace_period_minutes = 15;
    public int $half_day_threshold_minutes = 240;
    public bool $is_active = true;
    public string $description = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:work_schedules,code,' . $this->scheduleId,
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_duration' => 'required|integer|min:0',
            'work_days' => 'required|array|min:1',
            'work_days.*' => 'integer|between:1,7',
            'is_flexible' => 'boolean',
            'grace_period_minutes' => 'required|integer|min:0',
            'half_day_threshold_minutes' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ];
    }

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->scheduleId = $id;
            $this->loadSchedule();
        } else {
            $this->work_days = [1, 2, 3, 4, 5];
        }
    }

    public function loadSchedule(): void
    {
        $schedule = WorkSchedule::findOrFail($this->scheduleId);

        $this->name = $schedule->name;
        $this->code = $schedule->code;
        $this->start_time = $schedule->start_time;
        $this->end_time = $schedule->end_time;
        $this->break_duration = $schedule->break_duration;
        $this->work_days = $schedule->work_days ?? [];
        $this->is_flexible = $schedule->is_flexible;
        $this->grace_period_minutes = $schedule->grace_period_minutes;
        $this->half_day_threshold_minutes = $schedule->half_day_threshold_minutes;
        $this->is_active = $schedule->is_active;
        $this->description = $schedule->description ?? '';
    }

    public function save(): mixed
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'break_duration' => $this->break_duration,
            'work_days' => $this->work_days,
            'is_flexible' => $this->is_flexible,
            'grace_period_minutes' => $this->grace_period_minutes,
            'half_day_threshold_minutes' => $this->half_day_threshold_minutes,
            'is_active' => $this->is_active,
            'description' => $this->description,
        ];

        if ($this->scheduleId) {
            WorkSchedule::findOrFail($this->scheduleId)->update($data);
            session()->flash('success', __('attendance.schedule_updated'));
        } else {
            WorkSchedule::create($data);
            session()->flash('success', __('attendance.schedule_created'));
        }

        return $this->redirect(route('hr.attendance.schedules.index'), navigate: true);
    }

    public function delete(): mixed
    {
        if (!$this->scheduleId) {
            return null;
        }

        $schedule = WorkSchedule::findOrFail($this->scheduleId);

        if ($schedule->employeeSchedules()->exists()) {
            session()->flash('error', __('attendance.schedule_in_use'));
            return null;
        }

        $schedule->delete();
        session()->flash('success', __('attendance.schedule_deleted'));

        return $this->redirect(route('hr.attendance.schedules.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.attendance.schedules.form');
    }
}
