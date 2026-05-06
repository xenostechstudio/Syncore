<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chatter timelines query activity_logs with
     *   WHERE model_type = ? AND model_id = ? ORDER BY created_at DESC LIMIT N
     * The existing two-column [model_type, model_id] index serves the WHERE
     * but Postgres still has to fetch matching rows and sort. Replacing it
     * with a composite [model_type, model_id, created_at] lets the planner
     * walk the index in order and stop at LIMIT.
     *
     * The standalone created_at index is retained — it serves the weekly
     * cleanup job's WHERE created_at < ? scan, which has no model filter.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(
                ['model_type', 'model_id', 'created_at'],
                'activity_logs_timeline_idx'
            );
            $table->dropIndex(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['model_type', 'model_id']);
            $table->dropIndex('activity_logs_timeline_idx');
        });
    }
};
