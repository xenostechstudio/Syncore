<?php

namespace App\Livewire\Settings\AuditTrail;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.settings')]
#[Title('Audit Trail')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $action = '';
    
    #[Url]
    public string $modelType = '';
    
    #[Url]
    public string $userId = '';
    
    #[Url]
    public string $dateFrom = '';
    
    #[Url]
    public string $dateTo = '';

    // Detail modal
    public bool $showDetailModal = false;
    public ?array $selectedActivity = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'action', 'modelType', 'userId', 'dateFrom', 'dateTo']);
    }

    public function showDetail(int $activityId): void
    {
        $activity = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('activity_logs.id', $activityId)
            ->select('activity_logs.*', 'users.name as causer_name', 'users.email as causer_email')
            ->first();
        
        if (!$activity) {
            return;
        }

        $properties = json_decode($activity->properties ?? '{}', true);

        $this->selectedActivity = [
            'id' => $activity->id,
            'description' => $activity->description,
            'action' => $activity->action,
            'model_type' => $activity->model_type ? class_basename($activity->model_type) : null,
            'model_id' => $activity->model_id,
            'model_name' => $activity->model_name,
            'causer_name' => $activity->causer_name ?? $activity->user_name ?? 'System',
            'causer_email' => $activity->causer_email,
            'created_at' => \Carbon\Carbon::parse($activity->created_at)->format('M d, Y \a\t H:i:s'),
            'created_at_diff' => \Carbon\Carbon::parse($activity->created_at)->diffForHumans(),
            'properties' => $properties,
            'old_values' => $properties['old'] ?? [],
            'new_values' => $properties['new'] ?? $properties['attributes'] ?? [],
            'ip_address' => $activity->ip_address,
            'user_agent' => $activity->user_agent,
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedActivity = null;
    }

    public function render()
    {
        $query = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.name as causer_name')
            ->orderByDesc('activity_logs.created_at');

        if ($this->search) {
            $searchTerm = strtolower($this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(activity_logs.description) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(activity_logs.model_name) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(users.name) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        if ($this->action) {
            $query->where('activity_logs.action', $this->action);
        }

        if ($this->modelType) {
            $query->where('activity_logs.model_type', 'ilike', "%{$this->modelType}%");
        }

        if ($this->userId) {
            $query->where('activity_logs.user_id', $this->userId);
        }

        if ($this->dateFrom) {
            $query->whereDate('activity_logs.created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('activity_logs.created_at', '<=', $this->dateTo);
        }

        // Get unique values for filters
        $actions = DB::table('activity_logs')->distinct()->pluck('action')->filter();
        $modelTypes = DB::table('activity_logs')
            ->distinct()
            ->whereNotNull('model_type')
            ->pluck('model_type')
            ->map(fn($type) => class_basename($type))
            ->unique()
            ->filter();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        // Statistics
        $stats = [
            'total' => DB::table('activity_logs')->count(),
            'today' => DB::table('activity_logs')->whereDate('created_at', today())->count(),
            'this_week' => DB::table('activity_logs')->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'by_action' => DB::table('activity_logs')
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
        ];

        return view('livewire.settings.audit-trail.index', [
            'activities' => $query->paginate(20),
            'actions' => $actions,
            'modelTypes' => $modelTypes,
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    public function getModelName(string $modelType): string
    {
        return class_basename($modelType);
    }

    public function formatValue($value): string
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }
        return (string) $value;
    }
}
