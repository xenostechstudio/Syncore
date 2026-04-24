<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            // Proof of Delivery
            $table->string('signature_image')->nullable()->after('notes');
            $table->string('delivery_photo')->nullable()->after('signature_image');
            $table->string('received_by')->nullable()->after('delivery_photo');
            
            // Delivery Instructions & Preferences
            $table->text('delivery_instructions')->nullable()->after('received_by');
            $table->string('preferred_time_slot')->nullable()->after('delivery_instructions');
            
            // Delivery Attempts & Exceptions
            $table->integer('delivery_attempts')->default(0)->after('preferred_time_slot');
            $table->timestamp('last_attempt_at')->nullable()->after('delivery_attempts');
            $table->string('failure_reason')->nullable()->after('last_attempt_at');
            
            // Performance Tracking
            $table->timestamp('picked_at')->nullable()->after('failure_reason');
            $table->timestamp('shipped_at')->nullable()->after('picked_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            
            // Delivery Cost & Insurance
            $table->decimal('shipping_cost', 15, 2)->nullable()->after('delivered_at');
            $table->decimal('insurance_amount', 15, 2)->nullable()->after('shipping_cost');
            
            // Customer Feedback
            $table->integer('customer_rating')->nullable()->after('insurance_amount');
            $table->text('customer_feedback')->nullable()->after('customer_rating');
            
            // Partial Delivery Support
            $table->boolean('is_partial')->default(false)->after('customer_feedback');
            $table->foreignId('parent_delivery_id')->nullable()->constrained('delivery_orders')->nullOnDelete()->after('is_partial');
            
            // Indexes
            $table->index('picked_at');
            $table->index('shipped_at');
            $table->index('delivered_at');
            $table->index('is_partial');
        });

        // Enhance delivery_order_items table
        Schema::table('delivery_order_items', function (Blueprint $table) {
            // Add missing fields from model
            $table->foreignId('product_id')->nullable()->after('sales_order_item_id')->constrained('products')->nullOnDelete();
            $table->text('description')->nullable()->after('product_id');
            
            // Rename columns to match model
            $table->renameColumn('quantity_to_deliver', 'quantity');
            
            // Add indexes
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['parent_delivery_id']);
            $table->dropIndex(['picked_at']);
            $table->dropIndex(['shipped_at']);
            $table->dropIndex(['delivered_at']);
            $table->dropIndex(['is_partial']);
            
            $table->dropColumn([
                'signature_image',
                'delivery_photo',
                'received_by',
                'delivery_instructions',
                'preferred_time_slot',
                'delivery_attempts',
                'last_attempt_at',
                'failure_reason',
                'picked_at',
                'shipped_at',
                'delivered_at',
                'shipping_cost',
                'insurance_amount',
                'customer_rating',
                'customer_feedback',
                'is_partial',
                'parent_delivery_id',
            ]);
        });

        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropIndex(['product_id']);
            $table->dropColumn(['product_id', 'description']);
            $table->renameColumn('quantity', 'quantity_to_deliver');
        });
    }
};
