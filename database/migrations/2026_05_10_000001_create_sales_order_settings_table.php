<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_order_settings', function (Blueprint $table) {
            $table->id();

            // Document numbering — replaces the hardcoded "SO/{year}/{seq}"
            // currently in HasYearlySequenceNumber. Every customer sees this
            // on every quote/order email + PDF.
            // Defaults preserve current production format ("SO00001" — no
            // separator, no year). Admins can switch to "SO/2026/00001",
            // "QUO-2026-001", etc. via the settings page.
            $table->string('doc_number_prefix', 20)->default('SO');
            $table->string('doc_number_separator', 5)->default('');
            $table->integer('doc_number_padding')->default(5);
            $table->boolean('doc_number_yearly_reset')->default(false);

            // Quotation behavior — drives the "valid for N days" line on
            // PDFs and emails. Hardcoded to 30 in pdf/sales-order.blade.php
            // before this migration.
            $table->integer('quotation_validity_days')->default(30);

            // Default text fields — saves typing on every quote.
            $table->text('default_terms')->nullable();
            $table->text('default_notes')->nullable();

            // Workflow toggles — each is a real feature gate, not just data.
            $table->boolean('auto_send_on_confirm')->default(false);
            $table->enum('stock_check_mode', ['allow', 'warn', 'block'])->default('warn');

            // Default payment term — pre-selects the dropdown on new orders.
            // Nullable foreign key; null = "ask every time".
            $table->foreignId('default_payment_term_id')
                ->nullable()
                ->constrained('payment_terms')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_settings');
    }
};
