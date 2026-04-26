<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The original create_hr_tables migration declares $table->softDeletes(),
        // but databases created before that line was added are missing the column,
        // and the Employee model uses HasSoftDeletes — every query hits the missing
        // `deleted_at IS NULL` predicate and fails.
        if (! Schema::hasColumn('employees', 'deleted_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('employees', 'deleted_at')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
