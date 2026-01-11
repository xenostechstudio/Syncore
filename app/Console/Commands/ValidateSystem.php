<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * System Validation Command
 * 
 * Validates database structure, model configurations, and system health.
 * Run after migrations or deployments to ensure system integrity.
 */
class ValidateSystem extends Command
{
    protected $signature = 'system:validate {--fix : Attempt to fix issues}';
    protected $description = 'Validate system configuration, database structure, and model setup';

    protected array $errors = [];
    protected array $warnings = [];
    protected array $passed = [];

    public function handle(): int
    {
        $this->info('🔍 Starting System Validation...');
        $this->newLine();

        $this->validateDatabaseConnection();
        $this->validateRequiredTables();
        $this->validateActivityLogsTable();
        $this->validateIndexes();
        $this->validateModels();
        $this->validateServices();

        $this->newLine();
        $this->displayResults();

        return count($this->errors) > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    protected function validateDatabaseConnection(): void
    {
        $this->info('Checking database connection...');

        try {
            DB::connection()->getPdo();
            $driver = DB::connection()->getDriverName();
            $this->passed[] = "Database connection successful ({$driver})";

            if ($driver !== 'pgsql') {
                $this->warnings[] = "Database driver is '{$driver}', expected 'pgsql'. Some features may not work correctly.";
            }
        } catch (\Exception $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
        }
    }

    protected function validateRequiredTables(): void
    {
        $this->info('Checking required tables...');

        $requiredTables = [
            'users',
            'activity_logs',
            'notes',
            'attachments',
            'customers',
            'products',
            'sales_orders',
            'invoices',
            'suppliers',
            'purchase_rfqs',
            'vendor_bills',
            'employees',
            'departments',
            'positions',
            'leave_types',
            'leave_requests',
            'payroll_periods',
            'payroll_items',
            'leads',
            'opportunities',
            'accounts',
            'journal_entries',
            'warehouses',
            'inventory_transfers',
            'inventory_adjustments',
            'delivery_orders',
        ];

        $missingTables = [];
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $missingTables[] = $table;
            }
        }

        if (empty($missingTables)) {
            $this->passed[] = "All " . count($requiredTables) . " required tables exist";
        } else {
            $this->errors[] = "Missing tables: " . implode(', ', $missingTables);
        }
    }

    protected function validateActivityLogsTable(): void
    {
        $this->info('Checking activity_logs table structure...');

        if (!Schema::hasTable('activity_logs')) {
            $this->errors[] = "activity_logs table does not exist";
            return;
        }

        $requiredColumns = [
            'id',
            'user_id',
            'user_name',
            'action',
            'model_type',
            'model_id',
            'model_name',
            'description',
            'properties',
            'ip_address',
            'user_agent',
            'created_at',
        ];

        $missingColumns = [];
        foreach ($requiredColumns as $column) {
            if (!Schema::hasColumn('activity_logs', $column)) {
                $missingColumns[] = $column;
            }
        }

        if (empty($missingColumns)) {
            $this->passed[] = "activity_logs table has all required columns";
        } else {
            $this->errors[] = "activity_logs missing columns: " . implode(', ', $missingColumns);
        }

        // Check indexes
        $indexes = Schema::getIndexes('activity_logs');
        $indexNames = collect($indexes)->pluck('name')->toArray();

        $requiredIndexes = [
            'activity_logs_model_type_model_id_index',
            'activity_logs_user_id_index',
            'activity_logs_action_index',
            'activity_logs_created_at_index',
        ];

        $missingIndexes = array_diff($requiredIndexes, $indexNames);
        if (empty($missingIndexes)) {
            $this->passed[] = "activity_logs has all required indexes";
        } else {
            $this->warnings[] = "activity_logs missing indexes: " . implode(', ', $missingIndexes);
        }
    }

    protected function validateIndexes(): void
    {
        $this->info('Checking performance indexes...');

        $tableIndexes = [
            'sales_orders' => ['status', 'customer_id'],
            'invoices' => ['status', 'customer_id'],
            'products' => ['status', 'sku', 'category_id'],
            'customers' => ['email', 'name'],
            'employees' => ['department_id', 'position_id', 'status'],
            'leads' => ['status', 'assigned_to'],
        ];

        $missingIndexes = [];
        foreach ($tableIndexes as $table => $columns) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $indexes = Schema::getIndexes($table);
            $indexColumns = collect($indexes)->flatMap(fn($i) => $i['columns'])->toArray();

            foreach ($columns as $column) {
                if (!in_array($column, $indexColumns)) {
                    $missingIndexes[] = "{$table}.{$column}";
                }
            }
        }

        if (empty($missingIndexes)) {
            $this->passed[] = "All critical performance indexes exist";
        } else {
            $this->warnings[] = "Missing performance indexes: " . implode(', ', array_slice($missingIndexes, 0, 5));
            if (count($missingIndexes) > 5) {
                $this->warnings[] = "... and " . (count($missingIndexes) - 5) . " more missing indexes";
            }
        }
    }

    protected function validateModels(): void
    {
        $this->info('Checking model configurations...');

        $modelsWithLogsActivity = [
            \App\Models\User::class,
            \App\Models\Sales\Customer::class,
            \App\Models\Sales\SalesOrder::class,
            \App\Models\Inventory\Product::class,
            \App\Models\Invoicing\Invoice::class,
        ];

        $missingTrait = [];
        foreach ($modelsWithLogsActivity as $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }

            $traits = class_uses_recursive($modelClass);
            if (!in_array(\App\Traits\LogsActivity::class, $traits)) {
                $missingTrait[] = class_basename($modelClass);
            }
        }

        if (empty($missingTrait)) {
            $this->passed[] = "Core models have LogsActivity trait";
        } else {
            $this->warnings[] = "Models missing LogsActivity trait: " . implode(', ', $missingTrait);
        }
    }

    protected function validateServices(): void
    {
        $this->info('Checking service classes...');

        $services = [
            \App\Services\ActivityLogService::class,
            \App\Services\DashboardService::class,
            \App\Services\PdfService::class,
            \App\Services\ExportService::class,
        ];

        $missingServices = [];
        foreach ($services as $service) {
            if (!class_exists($service)) {
                $missingServices[] = class_basename($service);
            }
        }

        if (empty($missingServices)) {
            $this->passed[] = "All required service classes exist";
        } else {
            $this->errors[] = "Missing service classes: " . implode(', ', $missingServices);
        }
    }

    protected function displayResults(): void
    {
        $this->info('═══════════════════════════════════════');
        $this->info('           VALIDATION RESULTS          ');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        if (!empty($this->passed)) {
            $this->info('✅ PASSED (' . count($this->passed) . ')');
            foreach ($this->passed as $message) {
                $this->line("   • {$message}");
            }
            $this->newLine();
        }

        if (!empty($this->warnings)) {
            $this->warn('⚠️  WARNINGS (' . count($this->warnings) . ')');
            foreach ($this->warnings as $message) {
                $this->line("   • {$message}");
            }
            $this->newLine();
        }

        if (!empty($this->errors)) {
            $this->error('❌ ERRORS (' . count($this->errors) . ')');
            foreach ($this->errors as $message) {
                $this->line("   • {$message}");
            }
            $this->newLine();
        }

        $total = count($this->passed) + count($this->warnings) + count($this->errors);
        $this->info("Total checks: {$total}");
        
        if (empty($this->errors)) {
            $this->info('🎉 System validation completed successfully!');
        } else {
            $this->error('System validation found issues that need attention.');
        }
    }
}
