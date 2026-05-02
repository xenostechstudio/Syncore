<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('purchase_rfq_id')->constrained('purchase_rfqs')->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('purchase_rfq_id');
            $table->index('warehouse_id');
            $table->index('received_at');
        });

        Schema::create('purchase_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_receipt_id')->constrained('purchase_receipts')->cascadeOnDelete();
            $table->foreignId('purchase_rfq_item_id')->nullable()->constrained('purchase_rfq_items')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('quantity_received', 15, 2)->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index('purchase_receipt_id');
            $table->index('purchase_rfq_item_id');
        });

        Schema::table('purchase_rfq_items', function (Blueprint $table) {
            $table->decimal('quantity_received', 15, 2)->default(0)->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_rfq_items', function (Blueprint $table) {
            $table->dropColumn('quantity_received');
        });

        Schema::dropIfExists('purchase_receipt_items');
        Schema::dropIfExists('purchase_receipts');
    }
};
