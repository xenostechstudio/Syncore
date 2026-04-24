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
            DB::statement('ALTER TABLE inventory_transfers DROP CONSTRAINT IF EXISTS inventory_transfers_status_check');
        }

        DB::table('inventory_transfers')->where('status', 'pending')->update(['status' => 'draft']);

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE inventory_transfers ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE inventory_transfers ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }
};
