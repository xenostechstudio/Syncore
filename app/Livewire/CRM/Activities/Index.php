<?php

namespace App\Livewire\CRM\Activities;

use App\Models\CRM\Activity;
use App\Models\CRM\Lead;
use App\Models\CRM\Opportunity;
use App\Models\Sales\Customer;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'CRM'])]
#[Title('Activities')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $type = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $filter = 'upcoming'; // upcoming, today, overdue, all

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    public string $activityType = 'call';
    public string $subject = '';
    public string $description = '';
    public string $relatedType = '';
    public ?int $relatedId = null;
    public ?string $scheduledAt = null;
    public ?int $durationMinutes = null;
    public string $activityStatus = 'planned';
    public ?int $assignedTo = null;

    protected function rules(): array
    {
        return [
            'activityType' => 'required|in:call,meeting,email,task,note',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'relatedType' => 'nullable|in:lead,opportunity,customer',
            'relatedId' => 'nullable|integer',
            'scheduledAt' => 'nullable|date',
            'durationMinutes' => 'nullable|integer|min:1',
            'activityStatus' => 'required|in:planned,completed,cancelled',
            'assignedTo' => 'nullable|exists:users,id',
        ];
    }

    public function openModal(?int $id = null): void
    {
        $this->resetValidation();
        
        if ($id) {
            $activity = Activity::findOrFail($id);
            $this->editingId = $id;
            $this->isEditing = true;
            $this->activityType = $activity->type;
            $this->subject = $activity->subject;
            $this->description = $activity->description ?? '';
            $this->relatedType = match ($activity->activitable_type) {
                Lead::class => 'lead',
                Opportunity::class => 'opportunity',
                Customer::class => 'customer',
                default => '',
            };
            $this->relatedId = $activity->activitable_id;
            $this->scheduledAt = $activity->scheduled_at?->format('Y-m-d\TH:i');
            $this->durationMinutes = $activity->duration_minutes;
            $this->activityStatus = $activity->status;
            $this->assignedTo = $activity->assigned_to;
        } else {
            $this->reset(['editingId', 'isEditing', 'subject', 'description', 'relatedType', 'relatedId', 'scheduledAt', 'durationMinutes', 'assignedTo']);
            $this->activityType = 'call';
            $this->activityStatus = 'planned';
            $this->assignedTo = auth()->id();
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $activitableType = match ($this->relatedType) {
            'lead' => Lead::class,
            'opportunity' => Opportunity::class,
            'customer' => Customer::class,
            default => null,
        };

        $data = [
            'type' => $this->activityType,
            'subject' => $this->subject,
            'description' => $this->description ?: null,
            'activitable_type' => $activitableType,
            'activitable_id' => $this->relatedId,
            'scheduled_at' => $this->scheduledAt ?: null,
            'duration_minutes' => $this->durationMinutes,
            'status' => $this->activityStatus,
            'assigned_to' => $this->assignedTo,
            'completed_at' => $this->activityStatus === 'completed' ? now() : null,
        ];

        if ($this->isEditing) {
            Activity::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Activity updated successfully.');
        } else {
            $data['created_by'] = auth()->id();
            Activity::create($data);
            session()->flash('success', 'Activity created successfully.');
        }

        $this->showModal = false;
    }

    public function markAsCompleted(int $id): void
    {
        $activity = Activity::findOrFail($id);
        $activity->markAsCompleted();
        session()->flash('success', 'Activity marked as completed.');
    }

    public function delete(int $id): void
    {
        Activity::findOrFail($id)->delete();
        session()->flash('success', 'Activity deleted successfully.');
    }

    public function render()
    {
        $query = Activity::query()
            ->with(['activitable', 'assignedTo', 'createdBy'])
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->status, fn ($q) => $q->where('status', $this->status));

        $query = match ($this->filter) {
            'upcoming' => $query->where('status', 'planned')->where('scheduled_at', '>=', now())->orderBy('scheduled_at'),
            'today' => $query->whereDate('scheduled_at', today())->orderBy('scheduled_at'),
            'overdue' => $query->where('status', 'planned')->where('scheduled_at', '<', now())->orderByDesc('scheduled_at'),
            default => $query->orderByDesc('created_at'),
        };

        return view('livewire.crm.activities.index', [
            'activities' => $query->paginate(20),
            'users' => User::orderBy('name')->get(),
            'leads' => Lead::orderBy('name')->get(),
            'opportunities' => Opportunity::orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'types' => Activity::getTypes(),
        ]);
    }
}
