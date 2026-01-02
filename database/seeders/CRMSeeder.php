<?php

namespace Database\Seeders;

use App\Models\CRM\Lead;
use App\Models\CRM\Pipeline;
use App\Models\CRM\Opportunity;
use App\Models\CRM\Activity;
use App\Models\User;
use Illuminate\Database\Seeder;

class CRMSeeder extends Seeder
{
    public function run(): void
    {
        // Create Pipeline Stages
        $stages = Pipeline::getDefaultStages();
        foreach ($stages as $stage) {
            Pipeline::create($stage);
        }

        $user = User::first();
        $pipelines = Pipeline::all();

        // Create Sample Leads
        $leads = [
            ['name' => 'John Smith', 'email' => 'john.smith@techcorp.com', 'phone' => '+62 812 3456 7890', 'company_name' => 'TechCorp Indonesia', 'job_title' => 'IT Manager', 'source' => 'website', 'status' => 'new'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah.j@retailplus.id', 'phone' => '+62 813 9876 5432', 'company_name' => 'RetailPlus', 'job_title' => 'Procurement Head', 'source' => 'referral', 'status' => 'contacted'],
            ['name' => 'Michael Chen', 'email' => 'mchen@globaltech.com', 'phone' => '+62 821 1234 5678', 'company_name' => 'GlobalTech Solutions', 'job_title' => 'CTO', 'source' => 'trade_show', 'status' => 'qualified'],
            ['name' => 'Lisa Wong', 'email' => 'lisa.wong@startup.io', 'phone' => '+62 822 8765 4321', 'company_name' => 'Startup.io', 'job_title' => 'CEO', 'source' => 'cold_call', 'status' => 'new'],
            ['name' => 'David Lee', 'email' => 'david@enterprise.co.id', 'phone' => '+62 811 2345 6789', 'company_name' => 'Enterprise Co', 'job_title' => 'Operations Director', 'source' => 'email_campaign', 'status' => 'contacted'],
        ];

        foreach ($leads as $leadData) {
            Lead::create(array_merge($leadData, [
                'assigned_to' => $user?->id,
            ]));
        }

        // Create Sample Opportunities
        $opportunities = [
            ['name' => 'TechCorp - IT Infrastructure Upgrade', 'expected_revenue' => 150000000, 'probability' => 25, 'pipeline_id' => $pipelines->where('name', 'Qualified')->first()?->id],
            ['name' => 'RetailPlus - POS System Implementation', 'expected_revenue' => 85000000, 'probability' => 50, 'pipeline_id' => $pipelines->where('name', 'Proposition')->first()?->id],
            ['name' => 'GlobalTech - Cloud Migration Project', 'expected_revenue' => 250000000, 'probability' => 75, 'pipeline_id' => $pipelines->where('name', 'Negotiation')->first()?->id],
            ['name' => 'Startup.io - Initial Setup Package', 'expected_revenue' => 35000000, 'probability' => 10, 'pipeline_id' => $pipelines->where('name', 'New')->first()?->id],
            ['name' => 'Enterprise Co - Annual Support Contract', 'expected_revenue' => 120000000, 'probability' => 50, 'pipeline_id' => $pipelines->where('name', 'Proposition')->first()?->id],
        ];

        foreach ($opportunities as $oppData) {
            $opp = Opportunity::create(array_merge($oppData, [
                'assigned_to' => $user?->id,
                'expected_close_date' => now()->addDays(rand(14, 90)),
            ]));

            // Create sample activity for each opportunity
            Activity::create([
                'type' => ['call', 'meeting', 'email'][rand(0, 2)],
                'subject' => 'Follow up on ' . $opp->name,
                'activitable_type' => Opportunity::class,
                'activitable_id' => $opp->id,
                'scheduled_at' => now()->addDays(rand(1, 14)),
                'status' => 'planned',
                'assigned_to' => $user?->id,
                'created_by' => $user?->id,
            ]);
        }
    }
}
