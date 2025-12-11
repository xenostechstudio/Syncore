<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricelists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('currency', 3)->default('IDR');
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('discount', 8, 2)->default(0); // Default discount
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pricelist items for specific product pricing
        Schema::create('pricelist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricelist_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->integer('min_quantity')->default(1);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricelist_items');
        Schema::dropIfExists('pricelists');
    }
};
