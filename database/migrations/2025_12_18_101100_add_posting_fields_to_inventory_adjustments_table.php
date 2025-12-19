<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->timestamp('posted_at')->nullable()->after('status');
            $table->foreignId('source_delivery_order_id')->nullable()->after('posted_at')->constrained('delivery_orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_adjustments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_delivery_order_id');
            $table->dropColumn('posted_at');
        });
    }
};
