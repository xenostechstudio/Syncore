<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds performance indexes for all modules.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sales Orders
        $this->addIndexIfColumnExists('sales_orders', 'status');
        $this->addIndexIfColumnExists('sales_orders', 'customer_id');
        $this->addIndexIfColumnExists('sales_orders', 'created_at');
        $this->addIndexIfColumnExists('sales_orders', 'order_date');

        // Invoices
        $this->addIndexIfColumnExists('invoices', 'status');
        $this->addIndexIfColumnExists('invoices', 'customer_id');
        $this->addIndexIfColumnExists('invoices', 'due_date');

        // Products
        $this->addIndexIfColumnExists('products', 'status');
        $this->addIndexIfColumnExists('products', 'sku');
        $this->addIndexIfColumnExists('products', 'category_id');

        // Customers
        $this->addIndexIfColumnExists('customers', 'email');
        $this->addIndexIfColumnExists('customers', 'name');

        // Suppliers
        $this->addIndexIfColumnExists('suppliers', 'is_active');
        $this->addIndexIfColumnExists('suppliers', 'name');

        // Vendor Bills
        $this->addIndexIfColumnExists('vendor_bills', 'status');
        $this->addIndexIfColumnExists('vendor_bills', 'supplier_id');
        $this->addIndexIfColumnExists('vendor_bills', 'due_date');

        // Purchase RFQs
        $this->addIndexIfColumnExists('purchase_rfqs', 'status');
        $this->addIndexIfColumnExists('purchase_rfqs', 'supplier_id');

        // Delivery Orders
        $this->addIndexIfColumnExists('delivery_orders', 'status');
        $this->addIndexIfColumnExists('delivery_orders', 'sales_order_id');

        // Delivery Returns
        $this->addIndexIfColumnExists('delivery_returns', 'status');
        $this->addIndexIfColumnExists('delivery_returns', 'delivery_order_id');

        // Employees
        $this->addIndexIfColumnExists('employees', 'department_id');
        $this->addIndexIfColumnExists('employees', 'position_id');
        $this->addIndexIfColumnExists('employees', 'status');
        $this->addIndexIfColumnExists('employees', 'user_id');

        // Leave Requests
        $this->addIndexIfColumnExists('leave_requests', 'employee_id');
        $this->addIndexIfColumnExists('leave_requests', 'status');
        $this->addIndexIfColumnExists('leave_requests', 'leave_type_id');

        // Payroll
        $this->addIndexIfColumnExists('payroll_periods', 'status');
        $this->addIndexIfColumnExists('payroll_items', 'payroll_period_id');
        $this->addIndexIfColumnExists('payroll_items', 'employee_id');

        // CRM Leads
        $this->addIndexIfColumnExists('leads', 'status');
        $this->addIndexIfColumnExists('leads', 'assigned_to');
        $this->addIndexIfColumnExists('leads', 'source');

        // CRM Opportunities
        $this->addIndexIfColumnExists('opportunities', 'pipeline_id');
        $this->addIndexIfColumnExists('opportunities', 'assigned_to');
        $this->addIndexIfColumnExists('opportunities', 'customer_id');

        // Accounting
        $this->addIndexIfColumnExists('journal_entries', 'status');
        $this->addIndexIfColumnExists('journal_entries', 'entry_date');
        $this->addIndexIfColumnExists('journal_lines', 'account_id');
        $this->addIndexIfColumnExists('journal_lines', 'journal_entry_id');
        $this->addIndexIfColumnExists('accounts', 'type');
        $this->addIndexIfColumnExists('accounts', 'code');
        $this->addIndexIfColumnExists('accounts', 'is_active');

        // Inventory
        $this->addIndexIfColumnExists('inventory_transfers', 'status');
        $this->addIndexIfColumnExists('inventory_transfers', 'source_warehouse_id');
        $this->addIndexIfColumnExists('inventory_transfers', 'destination_warehouse_id');
        $this->addIndexIfColumnExists('inventory_adjustments', 'status');
        $this->addIndexIfColumnExists('inventory_adjustments', 'warehouse_id');

        // Payments
        $this->addIndexIfColumnExists('payments', 'invoice_id');
        $this->addIndexIfColumnExists('payments', 'payment_date');
        $this->addIndexIfColumnExists('vendor_bill_payments', 'vendor_bill_id');

        // Polymorphic tables
        $this->addCompositeIndexIfColumnsExist('notes', ['notable_type', 'notable_id']);
        $this->addCompositeIndexIfColumnsExist('attachments', ['attachable_type', 'attachable_id']);
    }

    public function down(): void
    {
        $indexes = [
            'sales_orders' => ['status', 'customer_id', 'created_at', 'order_date'],
            'invoices' => ['status', 'customer_id', 'due_date'],
            'products' => ['status', 'sku', 'category_id'],
            'customers' => ['email', 'name'],
            'suppliers' => ['is_active', 'name'],
            'vendor_bills' => ['status', 'supplier_id', 'due_date'],
            'purchase_rfqs' => ['status', 'supplier_id'],
            'delivery_orders' => ['status', 'sales_order_id'],
            'delivery_returns' => ['status', 'delivery_order_id'],
            'employees' => ['department_id', 'position_id', 'status', 'user_id'],
            'leave_requests' => ['employee_id', 'status', 'leave_type_id'],
            'payroll_periods' => ['status'],
            'payroll_items' => ['payroll_period_id', 'employee_id'],
            'leads' => ['status', 'assigned_to', 'source'],
            'opportunities' => ['pipeline_id', 'assigned_to', 'customer_id'],
            'journal_entries' => ['status', 'entry_date'],
            'journal_lines' => ['account_id', 'journal_entry_id'],
            'accounts' => ['type', 'code', 'is_active'],
            'inventory_transfers' => ['status', 'source_warehouse_id', 'destination_warehouse_id'],
            'inventory_adjustments' => ['status', 'warehouse_id'],
            'payments' => ['invoice_id', 'payment_date'],
            'vendor_bill_payments' => ['vendor_bill_id'],
        ];

        foreach ($indexes as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    foreach ($columns as $column) {
                        try { $table->dropIndex([$column]); } catch (\Exception $e) {}
                    }
                });
            }
        }

        // Composite indexes
        if (Schema::hasTable('notes')) {
            Schema::table('notes', function (Blueprint $table) {
                try { $table->dropIndex(['notable_type', 'notable_id']); } catch (\Exception $e) {}
            });
        }
        if (Schema::hasTable('attachments')) {
            Schema::table('attachments', function (Blueprint $table) {
                try { $table->dropIndex(['attachable_type', 'attachable_id']); } catch (\Exception $e) {}
            });
        }
    }

    protected function addIndexIfColumnExists(string $table, string $column): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            return;
        }
        if ($this->hasIndex($table, "{$table}_{$column}_index")) {
            return;
        }
        Schema::table($table, fn(Blueprint $t) => $t->index($column));
    }

    protected function addCompositeIndexIfColumnsExist(string $table, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }
        $indexName = "{$table}_" . implode('_', $columns) . "_index";
        if ($this->hasIndex($table, $indexName)) {
            return;
        }
        Schema::table($table, fn(Blueprint $t) => $t->index($columns));
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))->contains('name', $indexName);
    }
};
