<?php

namespace App\Livewire\CRM;

use App\Models\CRM\Lead;
use App\Models\CRM\Opportunity;
use App\Models\CRM\Activity;
use App\Models\CRM\Pipeline;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.module', ['module' => 'CRM'])]
#[Title('CRM')]
class Index extends Component
{
    public function render()
    {
        $totalLeads = Lead::count();
        $newLeads = Lead::where('status', 'new')->count();
        $qualifiedLeads = Lead::where('status', 'qualified')->count();

        $totalOpportunities = Opportunity::count();
        $openOpportunities = Opportunity::whereNull('won_at')->whereNull('lost_at')->count();
        $expectedRevenue = Opportunity::whereNull('won_at')->whereNull('lost_at')->sum('expected_revenue');
        $weightedRevenue = Opportunity::whereNull('won_at')->whereNull('lost_at')
            ->selectRaw('SUM(expected_revenue * probability / 100) as weighted')
            ->value('weighted') ?? 0;

        $wonThisMonth = Opportunity::whereNotNull('won_at')
            ->whereMonth('won_at', now()->month)
            ->whereYear('won_at', now()->year)
            ->sum('expected_revenue');

        $upcomingActivities = Activity::with('activitable')
            ->where('status', 'planned')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $recentLeads = Lead::with('assignedTo')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('livewire.crm.index', [
            'totalLeads' => $totalLeads,
            'newLeads' => $newLeads,
            'qualifiedLeads' => $qualifiedLeads,
            'totalOpportunities' => $totalOpportunities,
            'openOpportunities' => $openOpportunities,
            'expectedRevenue' => $expectedRevenue,
            'weightedRevenue' => $weightedRevenue,
            'wonThisMonth' => $wonThisMonth,
            'upcomingActivities' => $upcomingActivities,
            'recentLeads' => $recentLeads,
        ]);
    }
}
