<?php

namespace App\Livewire\HR\Payroll\Components;

use App\Livewire\Concerns\WithNotes;
use App\Models\HR\SalaryComponent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Salary Component')]
class Form extends Component
{
    use WithNotes;

    public ?int $componentId = null;
    public ?SalaryComponent $component = null;

    public string $code = '';
    public string $name = '';
    public string $type = 'earning';
    public string $calculationType = 'fixed';
    public float $defaultAmount = 0;
    public ?float $percentage = null;
    public string $percentageOf = '';
    public bool $isTaxable = true;
    public bool $isActive = true;
    public int $sortOrder = 0;
    public string $description = '';

    protected function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('salary_components', 'code')->ignore($this->componentId),
            ],
            'name' => 'required|string|max:255',
            'type' => 'required|in:earning,deduction',
            'calculationType' => 'required|in:fixed,percentage',
            'defaultAmount' => 'required|numeric|min:0',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'percentageOf' => 'nullable|string|max:100',
            'isTaxable' => 'boolean',
            'isActive' => 'boolean',
            'sortOrder' => 'integer|min:0',
            'description' => 'nullable|string',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->componentId = $id;

        if ($id) {
            $this->component = SalaryComponent::findOrFail($id);
            $this->code = $this->component->code;
            $this->name = $this->component->name;
            $this->type = $this->component->type;
            $this->calculationType = $this->component->calculation_type;
            $this->defaultAmount = $this->component->default_amount;
            $this->percentage = $this->component->percentage;
            $this->percentageOf = $this->component->percentage_of ?? '';
            $this->isTaxable = $this->component->is_taxable;
            $this->isActive = $this->component->is_active;
            $this->sortOrder = $this->component->sort_order;
            $this->description = $this->component->description ?? '';
        } else {
            $this->code = SalaryComponent::generateCode();
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'calculation_type' => $this->calculationType,
            'default_amount' => $this->defaultAmount,
            'percentage' => $this->percentage,
            'percentage_of' => $this->percentageOf ?: null,
            'is_taxable' => $this->isTaxable,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
            'description' => $this->description ?: null,
        ];

        if ($this->componentId) {
            $this->component->update($data);
            session()->flash('success', 'Salary component updated.');
        } else {
            $this->component = SalaryComponent::create($data);
            $this->componentId = $this->component->id;
            session()->flash('success', 'Salary component created.');
            $this->redirect(route('hr.payroll.components.edit', $this->component->id), navigate: true);
        }
    }

    public function delete(): void
    {
        if (!$this->component) return;

        $this->component->delete();
        session()->flash('success', 'Salary component deleted.');
        $this->redirect(route('hr.payroll.components.index'), navigate: true);
    }

    protected function getNotableModel()
    {
        return $this->component;
    }

    public function getActivities()
    {
        if (!$this->componentId) {
            return collect();
        }

        $modelClass = SalaryComponent::class;

        // Get activity logs from custom activity_logs table
        $activities = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('activity_logs.model_type', $modelClass)
            ->where('activity_logs.model_id', $this->componentId)
            ->select('activity_logs.*', 'users.name as causer_name')
            ->orderByDesc('activity_logs.created_at')
            ->limit(20)
            ->get()
            ->map(fn($activity) => (object) [
                'id' => $activity->id,
                'type' => 'activity',
                'action' => $activity->action,
                'description' => $activity->description,
                'properties' => json_decode($activity->properties ?? '{}', true),
                'causer' => (object) ['name' => $activity->causer_name ?? $activity->user_name ?? 'System'],
                'created_at' => \Carbon\Carbon::parse($activity->created_at),
            ]);

        if ($this->component && method_exists($this->component, 'notes')) {
            $notes = $this->component->notes()
                ->with('user')
                ->latest()
                ->limit(20)
                ->get()
                ->map(function ($note) {
                    $note->type = 'note';
                    $note->causer = $note->user;
                    return $note;
                });

            return $activities->concat($notes)
                ->sortByDesc('created_at')
                ->values()
                ->take(30);
        }

        return $activities;
    }

    public function render()
    {
        return view('livewire.hr.payroll.components.form', [
            'activities' => $this->getActivities(),
            'componentCreatedAt' => $this->component?->created_at?->format('H:i'),
        ]);
    }
}
