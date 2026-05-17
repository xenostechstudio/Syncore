<?php

namespace App\Livewire\HR\Departments;

use App\Livewire\Concerns\WithNotes;
use App\Models\HR\Department;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Department')]
class Form extends Component
{
    use WithNotes;
    public ?int $departmentId = null;
    public ?Department $department = null;

    public string $name = '';
    public string $code = '';
    public ?int $parentId = null;
    public ?int $managerId = null;
    public string $description = '';
    public bool $isActive = true;

    protected function getNotableModel()
    {
        return $this->departmentId ? Department::find($this->departmentId) : null;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:departments,code,' . $this->departmentId,
            'parentId' => 'nullable|exists:departments,id',
            'managerId' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->departmentId = $id;

        if ($id) {
            $this->department = Department::findOrFail($id);
            $this->name = $this->department->name;
            $this->code = $this->department->code ?? '';
            $this->parentId = $this->department->parent_id;
            $this->managerId = $this->department->manager_id;
            $this->description = $this->department->description ?? '';
            $this->isActive = $this->department->is_active;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code ?: null,
            'parent_id' => $this->parentId,
            'manager_id' => $this->managerId,
            'description' => $this->description ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->departmentId) {
            $this->department->update($data);
            session()->flash('success', 'Department updated successfully.');
        } else {
            $this->department = Department::create($data);
            $this->departmentId = $this->department->id;
            session()->flash('success', 'Department created successfully.');
        }
    }

    public function delete(): void
    {
        if (!$this->department) return;

        $this->department->delete();
        session()->flash('success', 'Department deleted successfully.');
        $this->redirect(route('hr.departments.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.departments.form', [
            'departments' => Department::where('id', '!=', $this->departmentId ?? 0)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
