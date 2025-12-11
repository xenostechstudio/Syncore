<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('rate', 8, 4); // e.g., 11.0000 for 11%
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->enum('scope', ['sales', 'purchase', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->boolean('include_in_price')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
