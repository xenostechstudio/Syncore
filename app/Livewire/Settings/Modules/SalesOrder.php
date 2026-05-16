<?php

namespace App\Livewire\Settings\Modules;

use App\Livewire\Concerns\WithPermissions;
use App\Models\Sales\PaymentTerm;
use App\Models\Settings\SalesOrderSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('Sales Order Settings')]
class SalesOrder extends Component
{
    use WithPermissions;

    // Document numbering
    public string $doc_number_prefix = 'SO';
    public string $doc_number_separator = '';
    public int $doc_number_padding = 5;
    public bool $doc_number_yearly_reset = false;

    // Quotation
    public int $quotation_validity_days = 30;

    // Defaults
    public ?string $default_terms = null;
    public ?string $default_notes = null;

    // Workflow
    public bool $auto_send_on_confirm = false;
    public string $stock_check_mode = 'warn'; // allow | warn | block
    public ?int $default_payment_term_id = null;

    public function mount(): void
    {
        $settings = SalesOrderSetting::instance();

        $this->doc_number_prefix       = (string) $settings->doc_number_prefix;
        $this->doc_number_separator    = (string) $settings->doc_number_separator;
        $this->doc_number_padding      = (int) $settings->doc_number_padding;
        $this->doc_number_yearly_reset = (bool) $settings->doc_number_yearly_reset;
        $this->quotation_validity_days = (int) $settings->quotation_validity_days;
        $this->default_terms           = $settings->default_terms;
        $this->default_notes           = $settings->default_notes;
        $this->auto_send_on_confirm    = (bool) $settings->auto_send_on_confirm;
        $this->stock_check_mode        = (string) $settings->stock_check_mode;
        $this->default_payment_term_id = $settings->default_payment_term_id;
    }

    /**
     * Live-preview of the document number — what the next issued order
     * will look like with the current form values. Driven by the same
     * formatter the model trait uses, so what you see is what gets
     * issued.
     */
    public function getNumberPreviewProperty(): string
    {
        $padded = str_pad('1', max(1, $this->doc_number_padding), '0', STR_PAD_LEFT);
        $sep    = $this->doc_number_separator;
        $year   = (string) now()->year;

        return $this->doc_number_yearly_reset
            ? "{$this->doc_number_prefix}{$sep}{$year}{$sep}{$padded}"
            : "{$this->doc_number_prefix}{$sep}{$padded}";
    }

    public function rules(): array
    {
        return [
            'doc_number_prefix'       => 'required|string|max:20',
            'doc_number_separator'    => 'nullable|string|max:5',
            'doc_number_padding'      => 'required|integer|min:1|max:10',
            'doc_number_yearly_reset' => 'boolean',
            'quotation_validity_days' => 'required|integer|min:1|max:365',
            'default_terms'           => 'nullable|string|max:5000',
            'default_notes'           => 'nullable|string|max:5000',
            'auto_send_on_confirm'    => 'boolean',
            'stock_check_mode'        => 'required|in:allow,warn,block',
            'default_payment_term_id' => 'nullable|exists:payment_terms,id',
        ];
    }

    #[On('saveSalesOrderSettings')]
    public function save(): void
    {
        $this->authorizePermission('settings.edit');

        // try/finally so the Alpine "Saving…" spinner resets even when
        // validate() throws — same pattern as the Company / Email saves.
        try {
            $this->validate();

            $settings = SalesOrderSetting::instance();
            $settings->update([
                'doc_number_prefix'       => $this->doc_number_prefix,
                'doc_number_separator'    => $this->doc_number_separator ?? '',
                'doc_number_padding'      => $this->doc_number_padding,
                'doc_number_yearly_reset' => $this->doc_number_yearly_reset,
                'quotation_validity_days' => $this->quotation_validity_days,
                'default_terms'           => $this->default_terms,
                'default_notes'           => $this->default_notes,
                'auto_send_on_confirm'    => $this->auto_send_on_confirm,
                'stock_check_mode'        => $this->stock_check_mode,
                'default_payment_term_id' => $this->default_payment_term_id,
            ]);

            // Bust the per-process cache so the new values take effect.
            SalesOrderSetting::clearCache();

            session()->flash('success', 'Sales Order settings saved.');
        } finally {
            $this->dispatch('sales-order-saved');
        }
    }

    public function render()
    {
        return view('livewire.settings.modules.sales-order', [
            'paymentTerms' => PaymentTerm::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
