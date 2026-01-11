<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

        // Seed default currencies
        $this->seedDefaultCurrencies();
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('currencies');
    }

    protected function seedDefaultCurrencies(): void
    {
        $currencies = [
            [
                'code' => 'IDR',
                'name' => 'Indonesian Rupiah',
                'symbol' => 'Rp',
                'exchange_rate' => 1.000000,
                'decimal_places' => 0,
                'decimal_separator' => ',',
                'thousand_separator' => '.',
                'symbol_position' => 'before',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'exchange_rate' => 0.000063,
                'decimal_places' => 2,
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'symbol_position' => 'before',
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'exchange_rate' => 0.000058,
                'decimal_places' => 2,
                'decimal_separator' => ',',
                'thousand_separator' => '.',
                'symbol_position' => 'after',
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'SGD',
                'name' => 'Singapore Dollar',
                'symbol' => 'S$',
                'exchange_rate' => 0.000085,
                'decimal_places' => 2,
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'symbol_position' => 'before',
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'code' => 'MYR',
                'name' => 'Malaysian Ringgit',
                'symbol' => 'RM',
                'exchange_rate' => 0.000280,
                'decimal_places' => 2,
                'decimal_separator' => '.',
                'thousand_separator' => ',',
                'symbol_position' => 'before',
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            \DB::table('currencies')->insert(array_merge($currency, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
};
