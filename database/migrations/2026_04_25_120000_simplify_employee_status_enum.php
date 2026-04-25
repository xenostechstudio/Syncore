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
            DB::statement('ALTER TABLE employees DROP CONSTRAINT IF EXISTS employees_status_check');
        }

        // Collapse retired cases into the single INACTIVE bucket.
        DB::table('employees')->whereIn('status', ['suspended', 'resigned', 'terminated'])
            ->update(['status' => 'inactive']);

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE employees ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }

    public function down(): void
    {
        // No reverse — the original cases are not preserved.
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE employees ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }
};
