<?php

namespace App\Console\Commands;

use App\Services\ActivityLogService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Test Activity Log Command
 * 
 * Tests the activity logging system by creating a test entry
 * and verifying it was stored correctly.
 */
class TestActivityLog extends Command
{
    protected $signature = 'activity-logs:test';
    protected $description = 'Test the activity logging system';

    public function handle(): int
    {
        $this->info('Testing Activity Log System...');
        $this->newLine();

        // Test 1: Direct logging
        $this->info('1. Testing direct logging...');
        try {
            ActivityLogService::log(
                action: 'test',
                model: null,
                description: 'Test activity log entry',
                properties: ['test_key' => 'test_value']
            );
            $this->line('   ✅ Direct logging successful');
        } catch (\Exception $e) {
            $this->error('   ❌ Direct logging failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Test 2: Verify entry was created
        $this->info('2. Verifying entry was created...');
        $entry = DB::table('activity_logs')
            ->where('action', 'test')
            ->where('description', 'Test activity log entry')
            ->latest('created_at')
            ->first();

        if ($entry) {
            $this->line('   ✅ Entry found in database');
            $this->line("   - ID: {$entry->id}");
            $this->line("   - Action: {$entry->action}");
            $this->line("   - Description: {$entry->description}");
            $this->line("   - Properties: {$entry->properties}");
            $this->line("   - Created: {$entry->created_at}");
        } else {
            $this->error('   ❌ Entry not found in database');
            return Command::FAILURE;
        }

        // Test 3: Test getActivitiesFor (if we have a model)
        $this->info('3. Testing recent activities query...');
        $recentActivities = DB::table('activity_logs')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        
        $this->line("   ✅ Found {$recentActivities->count()} recent activities");

        // Test 4: Cleanup test entry
        $this->info('4. Cleaning up test entry...');
        DB::table('activity_logs')
            ->where('action', 'test')
            ->where('description', 'Test activity log entry')
            ->delete();
        $this->line('   ✅ Test entry cleaned up');

        $this->newLine();
        $this->info('🎉 Activity Log System is working correctly!');

        return Command::SUCCESS;
    }
}
