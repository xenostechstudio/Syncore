<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('delivery_number');
            $table->timestamp('share_token_expires_at')->nullable()->after('share_token');
        });

        // Generate share tokens for existing delivery orders.
        // Doing this row-by-row keeps the migration portable across sqlite/mysql/pgsql.
        DB::table('delivery_orders')
            ->whereNull('share_token')
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($row) {
                DB::table('delivery_orders')
                    ->where('id', $row->id)
                    ->update(['share_token' => Str::random(64)]);
            });
    }

    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropColumn(['share_token', 'share_token_expires_at']);
        });
    }
};
