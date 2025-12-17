<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('xendit_invoice_id')->nullable()->after('status');
            $table->string('xendit_invoice_url')->nullable()->after('xendit_invoice_id');
            $table->string('xendit_external_id')->nullable()->after('xendit_invoice_url');
            $table->string('xendit_status')->nullable()->after('xendit_external_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'xendit_invoice_id',
                'xendit_invoice_url',
                'xendit_external_id',
                'xendit_status',
            ]);
        });
    }
};
