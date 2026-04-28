<?php

namespace App\Livewire\Settings\Modules;

use App\Models\Settings\InvoiceSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('Invoice Settings')]
class Invoice extends Component
{
    // Payment Gateway - Xendit
    public bool $xenditEnabled = false;
    public string $xenditPublicKey = '';
    public string $xenditSecretKey = '';
    public bool $xenditTestMode = true;

    // Template Settings
    public string $template_style = 'modern';
    public string $primary_color = '#18181b';
    public string $accent_color = '#10b981';
    public bool $show_logo = true;
    public string $logo_position = 'left';
    public int $logo_size = 120;
    public string $invoice_title = 'INVOICE';
    public bool $show_status_badge = true;
    public bool $show_payment_info = true;
    public ?string $bank_name = null;
    public ?string $bank_account = null;
    public ?string $bank_holder = null;
    public ?string $bank_name_2 = null;
    public ?string $bank_account_2 = null;
    public ?string $bank_holder_2 = null;
    public bool $show_qr_code = false;
    public ?string $qr_code_content = null;
    public ?string $default_notes = null;
    public ?string $default_terms = null;
    public string $footer_text = 'Thank you for your business!';
    public bool $show_tax_breakdown = true;
    public bool $show_discount = true;
    public string $currency_symbol = 'Rp';
    public string $currency_position = 'before';
    public string $date_format = 'M d, Y';
    public string $number_format = 'id';
    public bool $show_watermark = true;
    public string $watermark_text = 'DRAFT';
    public bool $show_signature = false;
    public string $signature_label = 'Authorized Signature';

    public function mount()
    {
        // Load Xendit config
        $this->xenditEnabled = config('xendit.enabled', false);
        $this->xenditTestMode = config('xendit.test_mode', true);
        $this->xenditPublicKey = config('xendit.public_key', '');

        // Load Invoice Settings
        $settings = InvoiceSetting::instance();
        $this->template_style = $settings->template_style ?? 'modern';
        $this->primary_color = $settings->primary_color ?? '#18181b';
        $this->accent_color = $settings->accent_color ?? '#10b981';
        $this->show_logo = $settings->show_logo ?? true;
        $this->logo_position = $settings->logo_position ?? 'left';
        $this->logo_size = $settings->logo_size ?? 120;
        $this->invoice_title = $settings->invoice_title ?? 'INVOICE';
        $this->show_status_badge = $settings->show_status_badge ?? true;
        $this->show_payment_info = $settings->show_payment_info ?? true;
        $this->bank_name = $settings->bank_name;
        $this->bank_account = $settings->bank_account;
        $this->bank_holder = $settings->bank_holder;
        $this->bank_name_2 = $settings->bank_name_2;
        $this->bank_account_2 = $settings->bank_account_2;
        $this->bank_holder_2 = $settings->bank_holder_2;
        $this->show_qr_code = $settings->show_qr_code ?? false;
        $this->qr_code_content = $settings->qr_code_content;
        $this->default_notes = $settings->default_notes;
        $this->default_terms = $settings->default_terms;
        $this->footer_text = $settings->footer_text ?? 'Thank you for your business!';
        $this->show_tax_breakdown = $settings->show_tax_breakdown ?? true;
        $this->show_discount = $settings->show_discount ?? true;
        $this->currency_symbol = $settings->currency_symbol ?? 'Rp';
        $this->currency_position = $settings->currency_position ?? 'before';
        $this->date_format = $settings->date_format ?? 'M d, Y';
        $this->number_format = $settings->number_format ?? 'id';
        $this->show_watermark = $settings->show_watermark ?? true;
        $this->watermark_text = $settings->watermark_text ?? 'DRAFT';
        $this->show_signature = $settings->show_signature ?? false;
        $this->signature_label = $settings->signature_label ?? 'Authorized Signature';
    }

    #[On('saveInvoiceSettings')]
    public function save()
    {
        $settings = InvoiceSetting::instance();
        
        $settings->update([
            'template_style' => $this->template_style,
            'primary_color' => $this->primary_color,
            'accent_color' => $this->accent_color,
            'show_logo' => $this->show_logo,
            'logo_position' => $this->logo_position,
            'logo_size' => $this->logo_size,
            'invoice_title' => $this->invoice_title,
            'show_status_badge' => $this->show_status_badge,
            'show_payment_info' => $this->show_payment_info,
            'bank_name' => $this->bank_name,
            'bank_account' => $this->bank_account,
            'bank_holder' => $this->bank_holder,
            'bank_name_2' => $this->bank_name_2,
            'bank_account_2' => $this->bank_account_2,
            'bank_holder_2' => $this->bank_holder_2,
            'show_qr_code' => $this->show_qr_code,
            'qr_code_content' => $this->qr_code_content,
            'default_notes' => $this->default_notes,
            'default_terms' => $this->default_terms,
            'footer_text' => $this->footer_text,
            'show_tax_breakdown' => $this->show_tax_breakdown,
            'show_discount' => $this->show_discount,
            'currency_symbol' => $this->currency_symbol,
            'currency_position' => $this->currency_position,
            'date_format' => $this->date_format,
            'number_format' => $this->number_format,
            'show_watermark' => $this->show_watermark,
            'watermark_text' => $this->watermark_text,
            'show_signature' => $this->show_signature,
            'signature_label' => $this->signature_label,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Invoice settings saved successfully!');
        $this->dispatch('invoice-saved');
        session()->flash('success', 'Invoice settings saved successfully!');
    }

    public function render()
    {
        return view('livewire.settings.modules.invoice');
    }
}
