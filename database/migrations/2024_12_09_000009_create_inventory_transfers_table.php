<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('source_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('destination_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('transfer_date');
            $table->date('expected_arrival_date')->nullable();
            $table->enum('status', ['draft', 'pending', 'in_transit', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_transfer_id')->constrained('inventory_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->integer('received_quantity')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
    }
};
