<?php

namespace App\Livewire\CRM\Opportunities;

use App\Models\CRM\Opportunity;
use App\Models\CRM\Pipeline;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.module', ['module' => 'CRM'])]
#[Title('Opportunities')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $view = 'kanban';

    #[Url]
    public string $search = '';

    #[Url]
    public string $stage = '';

    public bool $showStats = false;

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    public function toggleStats(): void
    {
        $this->showStats = !$this->showStats;
    }

    public function goToPreviousPage(): void
    {
        $this->previousPage();
    }

    public function goToNextPage(): void
    {
        $this->nextPage();
    }

    public function moveToStage(int $opportunityId, int $pipelineId): void
    {
        $opportunity = Opportunity::findOrFail($opportunityId);
        $pipeline = Pipeline::findOrFail($pipelineId);

        $opportunity->update([
            'pipeline_id' => $pipelineId,
            'probability' => $pipeline->probability,
        ]);

        if ($pipeline->is_won) {
            $opportunity->update(['won_at' => now(), 'lost_at' => null]);
            session()->flash('success', "'{$opportunity->name}' marked as Won!");
        } elseif ($pipeline->is_lost) {
            $opportunity->update(['lost_at' => now(), 'won_at' => null]);
            session()->flash('success', "'{$opportunity->name}' marked as Lost.");
        } else {
            // Clear won/lost status if moving back to active stage
            $opportunity->update(['won_at' => null, 'lost_at' => null]);
            session()->flash('success', "'{$opportunity->name}' moved to {$pipeline->name}.");
        }
    }

    public function markAsWon(int $id): void
    {
        $opportunity = Opportunity::findOrFail($id);
        $opportunity->markAsWon();
        session()->flash('success', 'Opportunity marked as won!');
    }

    public function markAsLost(int $id, string $reason = ''): void
    {
        $opportunity = Opportunity::findOrFail($id);
        $opportunity->markAsLost($reason);
        session()->flash('success', 'Opportunity marked as lost.');
    }

    public function delete(int $id): void
    {
        Opportunity::findOrFail($id)->delete();
        session()->flash('success', 'Opportunity deleted successfully.');
    }

    protected function getStatistics(): array
    {
        $baseQuery = Opportunity::query()
            ->whereNull('won_at')
            ->whereNull('lost_at');

        $pipelines = Pipeline::where('is_active', true)->orderBy('sequence')->get();
        $pipelineStats = [];

        foreach ($pipelines as $pipeline) {
            $pipelineStats[$pipeline->id] = [
                'name' => $pipeline->name,
                'color' => $pipeline->color ?? '#6b7280',
                'count' => (clone $baseQuery)->where('pipeline_id', $pipeline->id)->count(),
                'total' => (clone $baseQuery)->where('pipeline_id', $pipeline->id)->sum('expected_revenue'),
            ];
        }

        return [
            'total_opportunities' => (clone $baseQuery)->count(),
            'total_revenue' => (clone $baseQuery)->sum('expected_revenue'),
            'won_count' => Opportunity::whereNotNull('won_at')->count(),
            'won_revenue' => Opportunity::whereNotNull('won_at')->sum('expected_revenue'),
            'pipelines' => $pipelineStats,
        ];
    }

    public function render()
    {
        $pipelines = Pipeline::where('is_active', true)
            ->orderBy('sequence')
            ->get();

        $opportunitiesQuery = Opportunity::query()
            ->with(['customer', 'pipeline', 'assignedTo'])
            ->when($this->search, fn ($q) => $q->where('name', 'ilike', "%{$this->search}%"))
            ->when($this->stage, fn ($q) => $q->where('pipeline_id', $this->stage));

        if ($this->view === 'kanban') {
            // Include all opportunities in kanban (including won/lost)
            $opportunities = $opportunitiesQuery
                ->orderByDesc('expected_revenue')
                ->get()
                ->groupBy('pipeline_id');
        } else {
            $opportunities = $opportunitiesQuery
                ->orderByDesc('created_at')
                ->paginate(20);
        }

        return view('livewire.crm.opportunities.index', [
            'pipelines' => $pipelines,
            'opportunities' => $opportunities,
            'statistics' => $this->showStats ? $this->getStatistics() : null,
        ]);
    }
}
