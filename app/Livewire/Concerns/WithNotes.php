<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait WithNotes
{
    public string $noteContent = '';

    public function addNote(): void
    {
        if (empty(trim($this->noteContent))) {
            return;
        }

        $model = $this->getNotableModel();
        
        if (!$model || !method_exists($model, 'addNote')) {
            session()->flash('error', 'Cannot add note to this record.');
            return;
        }

        $model->addNote($this->noteContent, true);
        $this->noteContent = '';
        
        session()->flash('success', 'Note added successfully.');
    }

    /**
     * Get combined activities and notes for the model, sorted by date descending.
     * This is a computed property that can be accessed as $this->activitiesAndNotes
     */
    public function getActivitiesAndNotesProperty(): Collection
    {
        $model = $this->getNotableModel();
        
        if (!$model) {
            return collect();
        }

        $modelClass = get_class($model);

        // Get activity logs from custom activity_logs table
        $activities = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('activity_logs.model_type', $modelClass)
            ->where('activity_logs.model_id', $model->id)
            ->select('activity_logs.*', 'users.name as causer_name')
            ->orderByDesc('activity_logs.created_at')
            ->get()
            ->map(fn($activity) => [
                'type' => 'activity',
                'data' => (object) [
                    'id' => $activity->id,
                    'action' => $activity->action,
                    'description' => $activity->description,
                    'properties' => json_decode($activity->properties ?? '{}', true),
                    'causer' => (object) [
                        'id' => $activity->user_id,
                        'name' => $activity->causer_name ?? $activity->user_name ?? 'System',
                    ],
                    'created_at' => \Carbon\Carbon::parse($activity->created_at),
                ],
                'created_at' => \Carbon\Carbon::parse($activity->created_at),
            ]);

        // Get notes
        $notes = $model->notes()->with('user')->get()->map(fn($note) => [
            'type' => 'note',
            'data' => $note,
            'created_at' => $note->created_at,
        ]);

        // Merge and sort by created_at descending
        return $activities->concat($notes)
            ->sortByDesc('created_at')
            ->take(30)
            ->values();
    }

    /**
     * Override this method in your Livewire component to return the model instance
     */
    protected function getNotableModel()
    {
        // Default implementation - override in component
        return null;
    }
}
