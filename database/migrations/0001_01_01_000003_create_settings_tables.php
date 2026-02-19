<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Company Profile
        Schema::create('company_profile', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('logo')->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->timestamps();
        });

        // Email Configuration
        Schema::create('email_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver')->default('smtp');
            $table->string('host')->nullable();
            $table->integer('port')->nullable();
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->string('encryption')->nullable();
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Currencies
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('name');
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 15, 6)->default(1.000000);
            $table->tinyInteger('decimal_places')->default(2);
            $table->string('decimal_separator', 1)->default('.');
            $table->string('thousand_separator', 1)->default(',');
            $table->enum('symbol_position', ['before', 'after'])->default('before');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('is_default');
            $table->index('is_active');
        });

        // Exchange Rates
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('to_currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->decimal('rate', 15, 6);
            $table->date('rate_date');
            $table->string('source')->default('manual');
            $table->timestamps();
            $table->unique(['from_currency_id', 'to_currency_id', 'rate_date']);
            $table->index(['from_currency_id', 'to_currency_id']);
            $table->index('rate_date');
        });

        // Invoice Settings
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('template_style', ['modern', 'classic', 'minimal'])->default('modern');
            $table->string('primary_color', 7)->default('#18181b');
            $table->string('accent_color', 7)->default('#10b981');
            $table->boolean('show_logo')->default(true);
            $table->enum('logo_position', ['left', 'center', 'right'])->default('left');
            $table->integer('logo_size')->default(120);
            $table->string('invoice_title')->default('INVOICE');
            $table->string('invoice_prefix')->default('INV');
            $table->boolean('show_status_badge')->default(true);
            $table->boolean('show_payment_info')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_holder')->nullable();
            $table->string('bank_name_2')->nullable();
            $table->string('bank_account_2')->nullable();
            $table->string('bank_holder_2')->nullable();
            $table->boolean('show_qr_code')->default(false);
            $table->text('qr_code_content')->nullable();
            $table->text('default_notes')->nullable();
            $table->text('default_terms')->nullable();
            $table->string('footer_text')->default('Thank you for your business!');
            $table->boolean('show_tax_breakdown')->default(true);
            $table->boolean('show_discount')->default(true);
            $table->boolean('show_item_tax')->default(false);
            $table->string('currency_symbol', 10)->default('Rp');
            $table->string('currency_position', 10)->default('before');
            $table->string('date_format', 20)->default('M d, Y');
            $table->string('number_format', 20)->default('id');
            $table->boolean('show_watermark')->default(true);
            $table->string('watermark_text')->default('DRAFT');
            $table->boolean('show_signature')->default(false);
            $table->string('signature_label')->default('Authorized Signature');
            $table->timestamps();
        });

        // Permission Tables (Spatie)
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        // Activity Log (Spatie)
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable();
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });

        // Notes (Polymorphic)
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->morphs('notable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('content');
            $table->boolean('is_internal')->default(true);
            $table->timestamps();
        });

        // Attachments (Polymorphic)
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->text('description')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // System Notifications
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('icon')->default('bell');
            $table->string('color')->default('blue');
            $table->string('action_url')->nullable();
            $table->nullableMorphs('notifiable');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
        });

        // Activity Logs (Custom)
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('action', 50);
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('model_name')->nullable();
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['model_type', 'model_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
            $table->index('session_id');
        });

        // Seed default currencies
        $this->seedDefaultCurrencies();
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('invoice_settings');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('email_configurations');
        Schema::dropIfExists('company_profile');
    }

    protected function seedDefaultCurrencies(): void
    {
        $currencies = [
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'exchange_rate' => 1.000000, 'decimal_places' => 0, 'decimal_separator' => ',', 'thousand_separator' => '.', 'symbol_position' => 'before', 'is_default' => true, 'is_active' => true],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate' => 0.000063, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousand_separator' => ',', 'symbol_position' => 'before', 'is_default' => false, 'is_active' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'exchange_rate' => 0.000058, 'decimal_places' => 2, 'decimal_separator' => ',', 'thousand_separator' => '.', 'symbol_position' => 'after', 'is_default' => false, 'is_active' => true],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$', 'exchange_rate' => 0.000085, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousand_separator' => ',', 'symbol_position' => 'before', 'is_default' => false, 'is_active' => true],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'exchange_rate' => 0.000280, 'decimal_places' => 2, 'decimal_separator' => '.', 'thousand_separator' => ',', 'symbol_position' => 'before', 'is_default' => false, 'is_active' => true],
        ];

        foreach ($currencies as $currency) {
            \DB::table('currencies')->insert(array_merge($currency, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
};
