<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sales_order_items', 'tax_id')) {
                $table->foreignId('tax_id')
                    ->nullable()
                    ->after('inventory_item_id')
                    ->constrained('taxes')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('sales_order_items', 'tax_id')) {
                $table->dropConstrainedForeignId('tax_id');
            }
        });
    }
};
