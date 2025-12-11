<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique();
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Delivery person
            $table->date('delivery_date');
            $table->date('actual_delivery_date')->nullable();
            $table->enum('status', ['pending', 'picked', 'in_transit', 'delivered', 'failed', 'returned'])->default('pending');
            $table->text('shipping_address');
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('courier')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_order_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity_to_deliver');
            $table->integer('quantity_delivered')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
    }
};
