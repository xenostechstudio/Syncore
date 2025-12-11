<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('adjustment_date');
            $table->enum('adjustment_type', ['increase', 'decrease', 'count'])->default('count');
            $table->enum('status', ['draft', 'pending', 'approved', 'completed', 'cancelled'])->default('draft');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_adjustment_id')->constrained('inventory_adjustments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('system_quantity');
            $table->integer('counted_quantity');
            $table->integer('difference');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustment_items');
        Schema::dropIfExists('inventory_adjustments');
    }
};
