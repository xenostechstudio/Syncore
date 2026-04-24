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

        // Generate share tokens for existing delivery orders
        DB::table('delivery_orders')->whereNull('share_token')->update([
            'share_token' => DB::raw("md5(random()::text || clock_timestamp()::text)"),
        ]);
    }

    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropUnique(['share_token']);
            $table->dropColumn(['share_token', 'share_token_expires_at']);
        });
    }
};
