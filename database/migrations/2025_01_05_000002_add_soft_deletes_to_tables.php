<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'sales_orders',
        'sales_order_items',
        'invoices',
        'invoice_items',
        'purchase_rfqs',
        'purchase_rfq_items',
        'vendor_bills',
        'vendor_bill_items',
        'delivery_orders',
        'delivery_order_items',
        'inventory_adjustments',
        'inventory_transfers',
        'customers',
        'suppliers',
        'products',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
