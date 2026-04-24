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

        DB::table('employees')->where('status', 'inactive')->update(['status' => 'suspended']);

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE employees ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }

    public function down(): void
    {
        DB::table('employees')->where('status', 'suspended')->update(['status' => 'inactive']);

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE employees ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }
};
