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
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('status')->default('open')->after('pipeline_id');
            $table->index('status');
        });

        // Update existing opportunities based on won_at/lost_at
        DB::table('opportunities')
            ->whereNotNull('won_at')
            ->update(['status' => 'won']);

        DB::table('opportunities')
            ->whereNotNull('lost_at')
            ->update(['status' => 'lost']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
