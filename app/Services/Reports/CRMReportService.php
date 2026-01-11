<?php

namespace App\Services\Reports;

use App\Models\CRM\Lead;
use App\Models\CRM\Opportunity;
use App\Models\CRM\Pipeline;
use App\Models\CRM\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * CRM Report Service
 * 
 * Provides CRM analytics including lead conversion, pipeline analysis,
 * opportunity forecasting, and activity metrics.
 */
class CRMReportService
{
    /**
     * Get lead conversion funnel.
     */
    public function getLeadConversionFunnel(Carbon $startDate, Carbon $endDate): array
    {
        $leads = Lead::whereBetween('created_at', [$startDate, $endDate]);

        $total = (clone $leads)->count();
        $qualified = (clone $leads)->whereIn('status', ['qualified', 'converted'])->count();
        $converted = (clone $leads)->where('status', 'converted')->count();

        return [
            'total_leads' => $total,
            'qualified_leads' => $qualified,
            'converted_leads' => $converted,
            'qualification_rate' => $total > 0 ? round(($qualified / $total) * 100, 1) : 0,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
        ];
    }

    /**
     * Get leads by source.
     */
    public function getLeadsBySource(Carbon $startDate, Carbon $endDate): array
    {
        return Lead::query()
            ->select('source')
            ->selectRaw('COUNT(*) as lead_count')
            ->selectRaw("COUNT(CASE WHEN status = 'converted' THEN 1 END) as converted_count")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('source')
            ->orderByDesc('lead_count')
            ->get()
            ->map(fn($item) => [
                'source' => ucfirst($item->source ?? 'Unknown'),
                'lead_count' => $item->lead_count,
                'converted_count' => $item->converted_count,
                'conversion_rate' => $item->lead_count > 0 
                    ? round(($item->converted_count / $item->lead_count) * 100, 1) 
                    : 0,
            ])
            ->toArray();
    }

    /**
     * Get pipeline analysis.
     */
    public function getPipelineAnalysis(): array
    {
        return Pipeline::query()
            ->select('pipelines.*')
            ->selectRaw('COUNT(opportunities.id) as opportunity_count')
            ->selectRaw('COALESCE(SUM(opportunities.expected_revenue), 0) as total_value')
            ->selectRaw('COALESCE(AVG(opportunities.expected_revenue), 0) as avg_value')
            ->leftJoin('opportunities', function ($join) {
                $join->on('pipelines.id', '=', 'opportunities.pipeline_id')
                    ->whereNull('opportunities.won_at')
                    ->whereNull('opportunities.lost_at');
            })
            ->groupBy('pipelines.id')
            ->orderBy('pipelines.sequence')
            ->get()
            ->map(fn($pipeline) => [
                'id' => $pipeline->id,
                'name' => $pipeline->name,
                'probability' => $pipeline->probability,
                'opportunity_count' => $pipeline->opportunity_count,
                'total_value' => $pipeline->total_value,
                'avg_value' => round($pipeline->avg_value, 2),
                'weighted_value' => round($pipeline->total_value * ($pipeline->probability / 100), 2),
            ])
            ->toArray();
    }


    /**
     * Get opportunity win/loss analysis.
     */
    public function getWinLossAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $opportunities = Opportunity::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        $total = (clone $opportunities)->count();
        $won = (clone $opportunities)->whereNotNull('won_at')->count();
        $lost = (clone $opportunities)->whereNotNull('lost_at')->count();
        $open = (clone $opportunities)->whereNull('won_at')->whereNull('lost_at')->count();

        $wonValue = (clone $opportunities)->whereNotNull('won_at')->sum('expected_revenue');
        $lostValue = (clone $opportunities)->whereNotNull('lost_at')->sum('expected_revenue');

        return [
            'total_opportunities' => $total,
            'won_count' => $won,
            'lost_count' => $lost,
            'open_count' => $open,
            'won_value' => $wonValue,
            'lost_value' => $lostValue,
            'win_rate' => ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0,
        ];
    }

    /**
     * Get sales forecast.
     */
    public function getSalesForecast(int $months = 3): array
    {
        $forecast = [];
        
        for ($i = 0; $i < $months; $i++) {
            $month = now()->addMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();

            $opportunities = Opportunity::query()
                ->whereNull('won_at')
                ->whereNull('lost_at')
                ->whereBetween('expected_close_date', [$startOfMonth, $endOfMonth])
                ->get();

            $totalValue = $opportunities->sum('expected_revenue');
            $weightedValue = $opportunities->sum(fn($opp) => 
                $opp->expected_revenue * (($opp->probability ?? 50) / 100)
            );

            $forecast[] = [
                'month' => $month->format('M Y'),
                'opportunity_count' => $opportunities->count(),
                'total_value' => $totalValue,
                'weighted_value' => round($weightedValue, 2),
            ];
        }

        return $forecast;
    }

    /**
     * Get salesperson performance.
     */
    public function getSalespersonPerformance(Carbon $startDate, Carbon $endDate): array
    {
        return Opportunity::query()
            ->select('assigned_to')
            ->selectRaw('COUNT(*) as total_opportunities')
            ->selectRaw("COUNT(CASE WHEN won_at IS NOT NULL THEN 1 END) as won_count")
            ->selectRaw("COUNT(CASE WHEN lost_at IS NOT NULL THEN 1 END) as lost_count")
            ->selectRaw('COALESCE(SUM(CASE WHEN won_at IS NOT NULL THEN expected_revenue END), 0) as won_value')
            ->with('assignedUser:id,name')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('assigned_to')
            ->orderByDesc('won_value')
            ->get()
            ->map(fn($item) => [
                'salesperson' => $item->assignedUser?->name ?? 'Unassigned',
                'total_opportunities' => $item->total_opportunities,
                'won_count' => $item->won_count,
                'lost_count' => $item->lost_count,
                'won_value' => $item->won_value,
                'win_rate' => ($item->won_count + $item->lost_count) > 0 
                    ? round(($item->won_count / ($item->won_count + $item->lost_count)) * 100, 1) 
                    : 0,
            ])
            ->toArray();
    }

    /**
     * Get activity metrics.
     */
    public function getActivityMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return Activity::query()
            ->select('type')
            ->selectRaw('COUNT(*) as activity_count')
            ->selectRaw("COUNT(CASE WHEN completed_at IS NOT NULL THEN 1 END) as completed_count")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('type')
            ->orderByDesc('activity_count')
            ->get()
            ->map(fn($item) => [
                'type' => ucfirst($item->type),
                'activity_count' => $item->activity_count,
                'completed_count' => $item->completed_count,
                'completion_rate' => $item->activity_count > 0 
                    ? round(($item->completed_count / $item->activity_count) * 100, 1) 
                    : 0,
            ])
            ->toArray();
    }

    /**
     * Get CRM summary metrics.
     */
    public function getSummary(): array
    {
        $openLeads = Lead::whereNotIn('status', ['converted', 'lost'])->count();
        $openOpportunities = Opportunity::whereNull('won_at')->whereNull('lost_at')->count();
        $pipelineValue = Opportunity::whereNull('won_at')->whereNull('lost_at')->sum('expected_revenue');
        
        $thisMonthWon = Opportunity::whereNotNull('won_at')
            ->whereMonth('won_at', now()->month)
            ->whereYear('won_at', now()->year)
            ->sum('expected_revenue');

        $overdueActivities = Activity::whereNull('completed_at')
            ->where('scheduled_at', '<', now())
            ->count();

        return [
            'open_leads' => $openLeads,
            'open_opportunities' => $openOpportunities,
            'pipeline_value' => $pipelineValue,
            'won_this_month' => $thisMonthWon,
            'overdue_activities' => $overdueActivities,
        ];
    }
}
