<?php

namespace App\Livewire\Sales\Teams;

use App\Livewire\Concerns\WithNotes;
use App\Models\Sales\SalesTeam;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'Sales'])]
#[Title('Sales Team')]
class Form extends Component
{
    use WithNotes, WithPagination;

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
    
    // Member search & pagination
    public string $memberSearch = '';
    public int $memberPerPage = 10;

    // Modal states
    public bool $showArchiveModal = false;
    public bool $showDeleteModal = false;

    // Timestamps
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    protected function getNotableModel()
    {
        return $this->teamId ? SalesTeam::find($this->teamId) : null;
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

    public function updatedMemberSearch(): void
    {
        $this->resetPage('availableMembers');
    }

    #[Computed]
    public function selectedMembers()
    {
        if (empty($this->member_ids)) {
            return collect();
        }
        
        $validIds = array_filter($this->member_ids, fn($id) => $id !== null);
        return User::whereIn('id', $validIds)->orderBy('name')->get();
    }

    #[Computed]
    public function availableUsers()
    {
        // Get users that are either:
        // 1. Not in any team
        // 2. Already in current team (so they show up when editing)
        // 3. Not already selected as members
        $query = User::query()
            ->whereDoesntHave('teams', function ($q) {
                // Exclude users in other teams (not current team)
                if ($this->teamId) {
                    $q->where('sales_team_id', '!=', $this->teamId);
                }
            })
            ->whereNotIn('id', array_filter($this->member_ids, fn($id) => $id !== null));
        
        if ($this->memberSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->memberSearch . '%')
                  ->orWhere('email', 'like', '%' . $this->memberSearch . '%');
            });
        }
        
        return $query->orderBy('name')->paginate($this->memberPerPage, ['*'], 'availableMembers');
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

        // Filter out null member IDs before syncing
        $validMemberIds = array_filter($this->member_ids, fn($id) => $id !== null);

        if ($this->teamId) {
            $team = SalesTeam::findOrFail($this->teamId);
            $team->update($data);
            $team->members()->sync($validMemberIds);
            session()->flash('success', 'Sales team updated successfully.');
        } else {
            $team = SalesTeam::create($data);
            $team->members()->sync($validMemberIds);
            session()->flash('success', 'Sales team created successfully.');
            $this->redirect(route('sales.teams.edit', $team->id), navigate: true);
        }
    }

    public function addMember(int $userId): void
    {
        if (!in_array($userId, $this->member_ids)) {
            $this->member_ids[] = $userId;
            unset($this->availableUsers);
            unset($this->selectedMembers);
        }
    }

    public function removeMember(int $userId): void
    {
        $this->member_ids = array_values(array_filter($this->member_ids, fn($id) => $id !== $userId));
        unset($this->availableUsers);
        unset($this->selectedMembers);
    }

    public function openArchiveModal(): void
    {
        $this->showArchiveModal = true;
    }

    public function openDeleteModal(): void
    {
        $this->showDeleteModal = true;
    }

    public function archive(): void
    {
        if ($this->teamId) {
            $team = SalesTeam::findOrFail($this->teamId);
            $team->update(['is_active' => false]);
            $this->is_active = false;
            $this->showArchiveModal = false;
            session()->flash('success', 'Sales team archived successfully.');
        }
    }

    public function restore(): void
    {
        if ($this->teamId) {
            $team = SalesTeam::findOrFail($this->teamId);
            $team->update(['is_active' => true]);
            $this->is_active = true;
            session()->flash('success', 'Sales team restored successfully.');
        }
    }

    public function delete(): void
    {
        if ($this->teamId) {
            // Only allow deletion of archived records
            if ($this->is_active) {
                session()->flash('error', 'Please archive this team before deleting.');
                return;
            }
            
            SalesTeam::destroy($this->teamId);
            session()->flash('success', 'Sales team deleted permanently.');
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
