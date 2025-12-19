<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id']);
        });

        DB::table('inventory_stocks')->insertUsing(
            ['warehouse_id', 'product_id', 'quantity', 'created_at', 'updated_at'],
            DB::table('products')
                ->whereNotNull('warehouse_id')
                ->selectRaw('warehouse_id, id as product_id, quantity, CURRENT_TIMESTAMP as created_at, CURRENT_TIMESTAMP as updated_at')
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stocks');
    }
};
