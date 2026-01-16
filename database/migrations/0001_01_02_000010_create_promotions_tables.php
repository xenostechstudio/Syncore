<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Promotions
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable();
            $table->enum('type', [
                'buy_x_get_y',      // Buy X Get Y free/discounted
                'bundle',           // Bundle pricing
                'quantity_break',   // Tiered quantity discounts
                'cart_discount',    // % or fixed off entire cart
                'product_discount', // Specific product discounts
                'coupon',           // Coupon code required
            ]);
            $table->integer('priority')->default(10); // Lower = higher priority
            $table->boolean('is_combinable')->default(false); // Can stack with other promos
            $table->boolean('requires_coupon')->default(false); // Requires code entry
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('usage_limit')->nullable(); // Total uses allowed
            $table->integer('usage_per_customer')->nullable(); // Uses per customer
            $table->integer('usage_count')->default(0); // Current usage count
            $table->decimal('min_order_amount', 15, 2)->nullable(); // Minimum cart value
            $table->integer('min_quantity')->nullable(); // Minimum items in cart
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Promotion Rules (conditions to qualify)
        Schema::create('promotion_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->enum('rule_type', [
                'product',          // Specific products
                'category',         // Product categories
                'customer',         // Specific customers
                'customer_group',   // Customer groups/tags
                'min_quantity',     // Minimum quantity of specific product
                'min_amount',       // Minimum amount spent
            ]);
            $table->enum('operator', ['in', 'not_in', '>=', '<=', '='])->default('in');
            $table->json('value'); // Product IDs, category IDs, customer IDs, etc.
            $table->timestamps();
        });

        // Promotion Rewards (what customer gets)
        Schema::create('promotion_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->enum('reward_type', [
                'free_product',      // Get free product(s)
                'discount_percent',  // Percentage discount
                'discount_fixed',    // Fixed amount discount
                'free_shipping',     // Free shipping
                'buy_x_get_y',       // Buy X get Y at discount
            ]);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); // For free product
            $table->integer('buy_quantity')->nullable(); // Buy X quantity
            $table->integer('get_quantity')->nullable(); // Get Y quantity
            $table->decimal('discount_value', 15, 2)->nullable(); // Discount amount or percentage
            $table->decimal('max_discount', 15, 2)->nullable(); // Cap for percentage discounts
            $table->enum('apply_to', ['order', 'product', 'cheapest', 'expensive'])->default('order');
            $table->timestamps();
        });

        // Promotion Usage Tracking
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->decimal('discount_amount', 15, 2);
            $table->timestamps();
        });

        // Add promotion reference to sales orders
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('pricelist_id')->constrained('promotions')->nullOnDelete();
            $table->string('promotion_code')->nullable()->after('promotion_id');
            $table->decimal('promotion_discount', 15, 2)->default(0)->after('promotion_code');
        });

        // Add promotion reference to sales order items
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->foreignId('promotion_id')->nullable()->after('tax_id')->constrained('promotions')->nullOnDelete();
            $table->decimal('promotion_discount', 15, 2)->default(0)->after('discount');
            $table->boolean('is_free_item')->default(false)->after('promotion_discount');
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn(['promotion_discount', 'is_free_item']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_id');
            $table->dropColumn(['promotion_code', 'promotion_discount']);
        });

        Schema::dropIfExists('promotion_usages');
        Schema::dropIfExists('promotion_rewards');
        Schema::dropIfExists('promotion_rules');
        Schema::dropIfExists('promotions');
    }
};
