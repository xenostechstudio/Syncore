<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Regular Shift", "Night Shift"
            $table->string('code')->unique(); // e.g., "REG", "NIGHT"
            $table->time('start_time'); // e.g., 09:00
            $table->time('end_time'); // e.g., 17:00
            $table->integer('break_duration')->default(60); // minutes
            $table->json('work_days')->nullable(); // [1,2,3,4,5] for Mon-Fri
            $table->boolean('is_flexible')->default(false);
            $table->integer('grace_period_minutes')->default(15);
            $table->integer('half_day_threshold_minutes')->default(240); // 4 hours
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
