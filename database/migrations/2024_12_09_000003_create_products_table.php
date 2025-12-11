<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable();
            $table->string('product_type')->default('goods'); // goods, service
            $table->string('internal_reference')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->unsignedBigInteger('sales_tax_id')->nullable();
            $table->string('status')->default('in_stock'); // in_stock, low_stock, out_of_stock
            $table->boolean('is_favorite')->default(false);
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->foreignId('responsible_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('volume', 10, 3)->nullable();
            $table->integer('customer_lead_time')->default(0); // in days
            $table->text('receipt_note')->nullable();
            $table->text('delivery_note')->nullable();
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
