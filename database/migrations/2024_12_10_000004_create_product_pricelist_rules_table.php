<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_pricelist_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('pricelist_id')->nullable()->constrained('pricelists')->nullOnDelete();
            $table->string('price_type')->default('fixed'); // fixed, discount
            $table->decimal('fixed_price', 15, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->integer('min_quantity')->default(1);
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_pricelist_rules');
    }
};
