<?php

namespace App\Livewire\Sales\Teams;

use App\Livewire\Concerns\WithNotes;
use App\Models\Sales\SalesTeam;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Team')]
class Form extends Component
{
    use WithNotes;

    public ?int $teamId = null;
    public string $type = 'team'; // 'team' or 'salesperson'
    
    // Team fields
    public string $name = '';
    public string $description = '';
    public ?int $leader_id = null;
    public ?float $target_amount = null;
    public bool $is_active = true;
    
    // Salesperson fields (when type is salesperson)
    public ?int $user_id = null;
    public ?int $sales_team_id = null;
    
    // Members
    public array $member_ids = [];

    // Timestamps
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    protected function getNotableModel()
    {
        return $this->teamId ? SalesTeam::find($this->teamId) : null;
    }

    public function getActivitiesAndNotesProperty(): \Illuminate\Support\Collection
    {
        if (!$this->teamId) {
            return collect();
        }

        $team = SalesTeam::find($this->teamId);
        
        // Get activity logs
        $activities = Activity::where('subject_type', SalesTeam::class)
            ->where('subject_id', $this->teamId)
            ->with('causer')
            ->get()
            ->map(function ($activity) {
                return [
                    'type' => 'activity',
                    'data' => $activity,
                    'created_at' => $activity->created_at,
                ];
            });

        // Get notes
        $notes = $team->notes()->with('user')->get()->map(function ($note) {
            return [
                'type' => 'note',
                'data' => $note,
                'created_at' => $note->created_at,
            ];
        });

        // Merge and sort by created_at descending
        return $activities->concat($notes)
            ->sortByDesc('created_at')
            ->take(30)
            ->values();
    }

    public function mount(?int $id = null, string $type = 'team'): void
    {
        $this->type = request()->query('type', $type);
        
        if ($id) {
            $this->teamId = $id;
            $team = SalesTeam::with(['leader', 'members'])->findOrFail($id);
            
            $this->name = $team->name;
            $this->description = $team->description ?? '';
            $this->leader_id = $team->leader_id;
            $this->target_amount = $team->target_amount;
            $this->is_active = $team->is_active;
            $this->member_ids = $team->members->pluck('id')->toArray();
            $this->createdAt = $team->created_at->format('M d, Y \a\t H:i');
            $this->updatedAt = $team->updated_at->format('M d, Y \a\t H:i');
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'leader_id' => 'nullable|exists:users,id',
            'target_amount' => 'nullable|numeric|min:0',
        ], [
            'name.required' => 'Please enter a team name.',
        ]);

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'leader_id' => $this->leader_id,
            'target_amount' => $this->target_amount,
            'is_active' => $this->is_active,
        ];

        if ($this->teamId) {
            $team = SalesTeam::findOrFail($this->teamId);
            $team->update($data);
            $team->members()->sync($this->member_ids);
            session()->flash('success', 'Sales team updated successfully.');
        } else {
            $team = SalesTeam::create($data);
            $team->members()->sync($this->member_ids);
            session()->flash('success', 'Sales team created successfully.');
            $this->redirect(route('sales.teams.edit', $team->id), navigate: true);
        }
    }

    public function delete(): void
    {
        if ($this->teamId) {
            SalesTeam::destroy($this->teamId);
            session()->flash('success', 'Sales team deleted successfully.');
            $this->redirect(route('sales.teams.index'), navigate: true);
        }
    }

    public function render()
    {
        $users = User::orderBy('name')->get();
        $teams = SalesTeam::where('id', '!=', $this->teamId)->orderBy('name')->get();

        return view('livewire.sales.teams.form', [
            'users' => $users,
            'teams' => $teams,
            'activities' => $this->activitiesAndNotes,
        ]);
    }
}
