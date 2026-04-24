<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('attendance_settings')->insert([
            [
                'key' => 'grace_period_minutes',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Grace period for late arrivals in minutes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'half_day_threshold_minutes',
                'value' => '240',
                'type' => 'integer',
                'description' => 'Minutes late before marking as half-day (4 hours)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'require_photo',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Require photo on check-in/check-out',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'require_location',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Require GPS location on check-in/check-out',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'office_location',
                'value' => '{"lat": 0, "lng": 0}',
                'type' => 'json',
                'description' => 'Office GPS coordinates',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'geofence_radius_meters',
                'value' => '500',
                'type' => 'integer',
                'description' => 'Allowed distance from office in meters',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'auto_checkout_time',
                'value' => '23:59',
                'type' => 'string',
                'description' => 'Automatic checkout time if employee forgets',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_settings');
    }
};
