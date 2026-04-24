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
            DB::statement('ALTER TABLE sales_orders DROP CONSTRAINT IF EXISTS sales_orders_status_check');
        }

        // Legacy 'shipped' has no mapping in SalesOrderState. Map to 'processing'
        // (= SalesOrderState::SALES_ORDER), the closest semantic state.
        DB::table('sales_orders')->where('status', 'shipped')->update(['status' => 'processing']);

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE sales_orders ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE sales_orders ALTER COLUMN status TYPE VARCHAR(20)');
        }
    }
};
