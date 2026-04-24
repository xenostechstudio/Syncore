<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing check constraint first
        DB::statement("ALTER TABLE employees DROP CONSTRAINT IF EXISTS employees_status_check");
        
        // Update existing status values to match new enum
        DB::table('employees')->where('status', 'inactive')->update(['status' => 'suspended']);
        
        // Change the column type to VARCHAR to allow new enum values
        DB::statement("ALTER TABLE employees ALTER COLUMN status TYPE VARCHAR(20)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status values
        DB::table('employees')->where('status', 'suspended')->update(['status' => 'inactive']);
        
        // Restore original enum
        DB::statement("ALTER TABLE employees ALTER COLUMN status TYPE VARCHAR(20)");
    }
};
