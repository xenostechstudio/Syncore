<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('email_configurations', 'driver') && ! Schema::hasColumn('email_configurations', 'mailer')) {
            Schema::table('email_configurations', function (Blueprint $table) {
                $table->renameColumn('driver', 'mailer');
            });
        }

        Schema::table('email_configurations', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('email_configurations', 'mailer') && ! Schema::hasColumn('email_configurations', 'driver')) {
            Schema::table('email_configurations', function (Blueprint $table) {
                $table->renameColumn('mailer', 'driver');
            });
        }

        Schema::table('email_configurations', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }
};
