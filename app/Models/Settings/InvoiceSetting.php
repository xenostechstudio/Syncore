<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    protected $fillable = [
        'template_style',
        'primary_color',
        'accent_color',
        'show_logo',
        'logo_position',
        'logo_size',
        'invoice_title',
        'invoice_prefix',
        'show_status_badge',
        'show_payment_info',
        'bank_name',
        'bank_account',
        'bank_holder',
        'bank_name_2',
        'bank_account_2',
        'bank_holder_2',
        'show_qr_code',
        'qr_code_content',
        'default_notes',
        'default_terms',
        'footer_text',
        'show_tax_breakdown',
        'show_discount',
        'show_item_tax',
        'currency_symbol',
        'currency_position',
        'date_format',
        'number_format',
        'show_watermark',
        'watermark_text',
        'show_signature',
        'signature_label',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_status_badge' => 'boolean',
        'show_payment_info' => 'boolean',
        'show_qr_code' => 'boolean',
        'show_tax_breakdown' => 'boolean',
        'show_discount' => 'boolean',
        'show_item_tax' => 'boolean',
        'show_watermark' => 'boolean',
        'show_signature' => 'boolean',
        'logo_size' => 'integer',
    ];

    /**
     * Get the singleton instance or create default
     */
    public static function instance(): self
    {
        $settings = static::first();
        
        if (!$settings) {
            $settings = static::create([
                'template_style' => 'modern',
                'primary_color' => '#18181b',
                'accent_color' => '#10b981',
                'show_logo' => true,
                'logo_position' => 'left',
                'logo_size' => 120,
                'invoice_title' => 'INVOICE',
                'invoice_prefix' => 'INV',
                'show_status_badge' => true,
                'show_payment_info' => true,
                'show_qr_code' => false,
                'footer_text' => 'Thank you for your business!',
                'show_tax_breakdown' => true,
                'show_discount' => true,
                'show_item_tax' => false,
                'currency_symbol' => 'Rp',
                'currency_position' => 'before',
                'date_format' => 'M d, Y',
                'number_format' => 'id',
                'show_watermark' => true,
                'watermark_text' => 'DRAFT',
                'show_signature' => false,
                'signature_label' => 'Authorized Signature',
            ]);
        }
        
        return $settings;
    }

    /**
     * Format currency amount
     */
    public function formatCurrency(float $amount): string
    {
        $formatted = $this->number_format === 'id' 
            ? number_format($amount, 0, ',', '.')
            : number_format($amount, 2, '.', ',');

        return $this->currency_position === 'before'
            ? "{$this->currency_symbol} {$formatted}"
            : "{$formatted} {$this->currency_symbol}";
    }

    /**
     * Format date
     */
    public function formatDate(?\Carbon\Carbon $date): string
    {
        return $date?->format($this->date_format) ?? '-';
    }
}
