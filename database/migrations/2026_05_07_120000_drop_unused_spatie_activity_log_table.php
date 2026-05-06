<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original settings-tables migration created an `activity_log`
     * (singular) table that Spatie's package expects. The project has
     * always written its own activity entries via App\Services\
     * ActivityLogService into `activity_logs` (plural); nothing reads
     * or writes the singular table.
     *
     * Dropping the table here, removing the (also unused) Spatie
     * package from composer, and editing the original migration so
     * fresh installs skip creating it.
     */
    public function up(): void
    {
        Schema::dropIfExists('activity_log');
    }

    public function down(): void
    {
        // No reverse — the table held no data. Reinstate the Spatie
        // package if you need this back.
    }
};
