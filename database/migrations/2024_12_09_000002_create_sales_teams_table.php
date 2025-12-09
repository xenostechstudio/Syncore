<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('leader_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sales_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_team_id')->constrained('sales_teams')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['sales_team_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_team_members');
        Schema::dropIfExists('sales_teams');
    }
};
