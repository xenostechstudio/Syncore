<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('work_schedule_id')->nullable()->constrained('work_schedules')->nullOnDelete();
            
            // Check-in details
            $table->time('check_in_time')->nullable();
            $table->string('check_in_location')->nullable(); // GPS coordinates
            $table->string('check_in_photo')->nullable();
            $table->string('check_in_device')->nullable(); // web/mobile/biometric
            $table->string('check_in_ip')->nullable();
            $table->text('check_in_notes')->nullable();
            
            // Check-out details
            $table->time('check_out_time')->nullable();
            $table->string('check_out_location')->nullable();
            $table->string('check_out_photo')->nullable();
            $table->string('check_out_device')->nullable();
            $table->string('check_out_ip')->nullable();
            $table->text('check_out_notes')->nullable();
            
            // Calculated fields
            $table->enum('status', [
                'present',
                'late',
                'absent',
                'half_day',
                'on_leave',
                'weekend',
                'holiday'
            ])->default('absent');
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->integer('work_duration_minutes')->default(0);
            
            // Manual entry/correction
            $table->boolean('is_manual')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['employee_id', 'date']);
            $table->index('date');
            $table->index('status');
            $table->index(['employee_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
