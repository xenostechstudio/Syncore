<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Sales person
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->text('shipping_address')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
