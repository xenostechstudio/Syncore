<?php

namespace App\Livewire\Settings\AuditTrail;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('components.layouts.settings')]
#[Title('Audit Trail')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';
    
    #[Url]
    public string $logName = '';
    
    #[Url]
    public string $event = '';
    
    #[Url]
    public string $causerId = '';
    
    #[Url]
    public string $dateFrom = '';
    
    #[Url]
    public string $dateTo = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'logName', 'event', 'causerId', 'dateFrom', 'dateTo']);
    }

    public function render()
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                  ->orWhere('subject_type', 'like', "%{$this->search}%");
            });
        }

        if ($this->logName) {
            $query->where('log_name', $this->logName);
        }

        if ($this->event) {
            $query->where('event', $this->event);
        }

        if ($this->causerId) {
            $query->where('causer_id', $this->causerId);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        // Get unique values for filters
        $logNames = Activity::distinct()->pluck('log_name')->filter();
        $events = Activity::distinct()->pluck('event')->filter();
        $users = \App\Models\User::orderBy('name')->get(['id', 'name']);

        return view('livewire.settings.audit-trail.index', [
            'activities' => $query->paginate(20),
            'logNames' => $logNames,
            'events' => $events,
            'users' => $users,
        ]);
    }

    public function getModelName(string $subjectType): string
    {
        return class_basename($subjectType);
    }
}
