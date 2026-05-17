<?php

namespace App\Livewire\HR\Leave\Types;

use App\Livewire\Concerns\WithNotes;
use App\Models\HR\LeaveType;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'HR'])]
#[Title('Leave Type')]
class Form extends Component
{
    use WithNotes;

    public ?int $leaveTypeId = null;
    public ?LeaveType $leaveType = null;

    public string $name = '';
    public string $code = '';
    public int $daysPerYear = 0;
    public bool $isPaid = true;
    public bool $requiresApproval = true;
    public bool $isActive = true;
    public string $description = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('leave_types', 'code')->ignore($this->leaveTypeId),
            ],
            'daysPerYear' => 'required|integer|min:0',
            'isPaid' => 'boolean',
            'requiresApproval' => 'boolean',
            'isActive' => 'boolean',
            'description' => 'nullable|string',
        ];
    }

    public function mount(?int $id = null): void
    {
        $this->leaveTypeId = $id;

        if ($id) {
            $this->leaveType = LeaveType::findOrFail($id);
            $this->name = $this->leaveType->name;
            $this->code = $this->leaveType->code;
            $this->daysPerYear = $this->leaveType->days_per_year;
            $this->isPaid = $this->leaveType->is_paid;
            $this->requiresApproval = $this->leaveType->requires_approval;
            $this->isActive = $this->leaveType->is_active;
            $this->description = $this->leaveType->description ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'days_per_year' => $this->daysPerYear,
            'is_paid' => $this->isPaid,
            'requires_approval' => $this->requiresApproval,
            'is_active' => $this->isActive,
            'description' => $this->description ?: null,
        ];

        if ($this->leaveTypeId) {
            $this->leaveType->update($data);
            session()->flash('success', 'Leave type updated successfully.');
        } else {
            $this->leaveType = LeaveType::create($data);
            $this->leaveTypeId = $this->leaveType->id;
            session()->flash('success', 'Leave type created successfully.');
            $this->redirect(route('hr.leave.types.edit', $this->leaveType->id), navigate: true);
        }
    }

    public function delete(): void
    {
        if (!$this->leaveType) return;

        $this->leaveType->delete();
        session()->flash('success', 'Leave type deleted successfully.');
        $this->redirect(route('hr.leave.types.index'), navigate: true);
    }

    protected function getNotableModel()
    {
        return $this->leaveType;
    }

    public function getActivities()
    {
        if (!$this->leaveTypeId) {
            return collect();
        }

        $modelClass = LeaveType::class;

        // Get activity logs from custom activity_logs table
        $activities = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('activity_logs.model_type', $modelClass)
            ->where('activity_logs.model_id', $this->leaveTypeId)
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

        // Get notes if model supports it
        if ($this->leaveType && method_exists($this->leaveType, 'notes')) {
            $notes = $this->leaveType->notes()
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
        return view('livewire.hr.leave.types.form', [
            'activities' => $this->getActivities(),
            'leaveTypeCreatedAt' => $this->leaveType?->created_at?->format('H:i'),
        ]);
    }
}
