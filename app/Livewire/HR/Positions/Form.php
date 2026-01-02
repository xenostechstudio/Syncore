<?php

namespace App\Livewire\HR\Positions;

use App\Models\HR\Department;
use App\Models\HR\Position;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Position')]
class Form extends Component
{
    public ?int $positionId = null;
    public ?Position $position = null;

    public string $name = '';
    public ?int $departmentId = null;
    public string $description = '';
    public string $requirements = '';
    public ?float $minSalary = null;
    public ?float $maxSalary = null;
    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'departmentId' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'minSalary' => 'nullable|numeric|min:0',
            'maxSalary' => 'nullable|numeric|min:0|gte:minSalary',
            'isActive' => 'boolean',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->positionId = $id;

        if ($id) {
            $this->position = Position::findOrFail($id);
            $this->name = $this->position->name;
            $this->departmentId = $this->position->department_id;
            $this->description = $this->position->description ?? '';
            $this->requirements = $this->position->requirements ?? '';
            $this->minSalary = $this->position->min_salary;
            $this->maxSalary = $this->position->max_salary;
            $this->isActive = $this->position->is_active;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'department_id' => $this->departmentId,
            'description' => $this->description ?: null,
            'requirements' => $this->requirements ?: null,
            'min_salary' => $this->minSalary,
            'max_salary' => $this->maxSalary,
            'is_active' => $this->isActive,
        ];

        if ($this->positionId) {
            $this->position->update($data);
            session()->flash('success', 'Position updated successfully.');
        } else {
            $this->position = Position::create($data);
            $this->positionId = $this->position->id;
            session()->flash('success', 'Position created successfully.');
        }
    }

    public function delete(): void
    {
        if (!$this->position) return;

        $this->position->delete();
        session()->flash('success', 'Position deleted successfully.');
        $this->redirect(route('hr.positions.index'), navigate: true);
    }

    public function getActivities()
    {
        if (!$this->positionId) {
            return collect();
        }

        return Activity::where('subject_type', Position::class)
            ->where('subject_id', $this->positionId)
            ->with('causer')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.hr.positions.form', [
            'departments' => Department::where('is_active', true)->orderBy('name')->get(),
            'activities' => $this->getActivities(),
        ]);
    }
}
