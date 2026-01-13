<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            
            // Template Style
            $table->enum('template_style', ['modern', 'classic', 'minimal'])->default('modern');
            $table->string('primary_color', 7)->default('#18181b'); // zinc-900
            $table->string('accent_color', 7)->default('#10b981'); // emerald-500
            
            // Logo
            $table->boolean('show_logo')->default(true);
            $table->enum('logo_position', ['left', 'center', 'right'])->default('left');
            $table->integer('logo_size')->default(120); // px width
            
            // Header
            $table->string('invoice_title')->default('INVOICE');
            $table->string('invoice_prefix')->default('INV');
            $table->boolean('show_status_badge')->default(true);
            
            // Payment Info
            $table->boolean('show_payment_info')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_holder')->nullable();
            $table->string('bank_name_2')->nullable();
            $table->string('bank_account_2')->nullable();
            $table->string('bank_holder_2')->nullable();
            
            // QR Code
            $table->boolean('show_qr_code')->default(false);
            $table->text('qr_code_content')->nullable(); // Template: {bank_account} or custom
            
            // Content
            $table->text('default_notes')->nullable();
            $table->text('default_terms')->nullable();
            $table->string('footer_text')->default('Thank you for your business!');
            
            // Display Options
            $table->boolean('show_tax_breakdown')->default(true);
            $table->boolean('show_discount')->default(true);
            $table->boolean('show_item_tax')->default(false);
            $table->string('currency_symbol', 10)->default('Rp');
            $table->string('currency_position', 10)->default('before'); // before/after
            $table->string('date_format', 20)->default('M d, Y');
            $table->string('number_format', 20)->default('id'); // id = 1.000, en = 1,000
            
            // Watermark
            $table->boolean('show_watermark')->default(true);
            $table->string('watermark_text')->default('DRAFT');
            
            // Signature
            $table->boolean('show_signature')->default(false);
            $table->string('signature_label')->default('Authorized Signature');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};
