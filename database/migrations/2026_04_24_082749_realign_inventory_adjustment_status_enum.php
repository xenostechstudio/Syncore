<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE inventory_adjustments DROP CONSTRAINT IF EXISTS inventory_adjustments_status_check');
        }

        DB::table('inventory_adjustments')
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'draft']);

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE inventory_adjustments ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE inventory_adjustments ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }
};
