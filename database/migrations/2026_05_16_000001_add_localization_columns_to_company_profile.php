<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profile', function (Blueprint $table) {
            $table->string('language', 10)->default('en')->after('timezone');
            $table->string('currency_symbol', 10)->default('Rp')->after('currency');
            $table->string('date_format', 30)->default('Y-m-d')->after('language');
            $table->string('time_format', 30)->default('H:i')->after('date_format');
        });
    }

    public function down(): void
    {
        Schema::table('company_profile', function (Blueprint $table) {
            $table->dropColumn(['language', 'currency_symbol', 'date_format', 'time_format']);
        });
    }
};
