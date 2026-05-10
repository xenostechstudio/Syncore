<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_settings', function (Blueprint $table) {
            $table->id();

            // Defaults preserve the existing form-generated format
            // ("RFQ-NNNNN") so installs don't see their numbering change.
            // Admins can flip on yearly_reset for "RFQ/2026/00001" or
            // rebrand to "PO" / "PUR" / etc. via the settings page.
            $table->string('doc_number_prefix', 20)->default('RFQ');
            $table->string('doc_number_separator', 5)->default('-');
            $table->integer('doc_number_padding')->default(5);
            $table->boolean('doc_number_yearly_reset')->default(false);

            // Default warehouse for receipts — most companies have one main
            // warehouse; null means "ask every time".
            $table->foreignId('default_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->nullOnDelete();

            // Default lead time — auto-fills expected_arrival_date on new POs.
            $table->integer('default_lead_time_days')->default(7);

            // Workflow toggles.
            $table->boolean('auto_send_to_supplier')->default(false);

            // Approval gate — POs above this amount require manager approval.
            // Null = no approval workflow (current behavior).
            $table->decimal('approval_threshold', 15, 2)->nullable();

            // Default text fields.
            $table->text('default_terms')->nullable();
            $table->text('default_notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_settings');
    }
};
