<?php

namespace App\Livewire\Settings\Modules;

use App\Models\Inventory\Warehouse;
use App\Models\Settings\PurchaseOrderSetting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('Purchase Order Settings')]
class PurchaseOrder extends Component
{
    // Document numbering
    public string $doc_number_prefix = 'RFQ';
    public string $doc_number_separator = '-';
    public int $doc_number_padding = 5;
    public bool $doc_number_yearly_reset = false;

    // Defaults
    public ?int $default_warehouse_id = null;
    public int $default_lead_time_days = 7;

    // Workflow
    public bool $auto_send_to_supplier = false;
    public ?float $approval_threshold = null;

    // Boilerplate text
    public ?string $default_terms = null;
    public ?string $default_notes = null;

    public function mount(): void
    {
        $settings = PurchaseOrderSetting::instance();

        $this->doc_number_prefix       = (string) $settings->doc_number_prefix;
        $this->doc_number_separator    = (string) $settings->doc_number_separator;
        $this->doc_number_padding      = (int) $settings->doc_number_padding;
        $this->doc_number_yearly_reset = (bool) $settings->doc_number_yearly_reset;
        $this->default_warehouse_id    = $settings->default_warehouse_id;
        $this->default_lead_time_days  = (int) $settings->default_lead_time_days;
        $this->auto_send_to_supplier   = (bool) $settings->auto_send_to_supplier;
        $this->approval_threshold      = $settings->approval_threshold !== null
            ? (float) $settings->approval_threshold
            : null;
        $this->default_terms           = $settings->default_terms;
        $this->default_notes           = $settings->default_notes;
    }

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
            'default_warehouse_id'    => 'nullable|exists:warehouses,id',
            'default_lead_time_days'  => 'required|integer|min:0|max:365',
            'auto_send_to_supplier'   => 'boolean',
            'approval_threshold'      => 'nullable|numeric|min:0',
            'default_terms'           => 'nullable|string|max:5000',
            'default_notes'           => 'nullable|string|max:5000',
        ];
    }

    #[On('savePurchaseOrderSettings')]
    public function save(): void
    {
        $this->validate();

        $settings = PurchaseOrderSetting::instance();
        $settings->update([
            'doc_number_prefix'       => $this->doc_number_prefix,
            'doc_number_separator'    => $this->doc_number_separator ?? '',
            'doc_number_padding'      => $this->doc_number_padding,
            'doc_number_yearly_reset' => $this->doc_number_yearly_reset,
            'default_warehouse_id'    => $this->default_warehouse_id,
            'default_lead_time_days'  => $this->default_lead_time_days,
            'auto_send_to_supplier'   => $this->auto_send_to_supplier,
            'approval_threshold'      => $this->approval_threshold,
            'default_terms'           => $this->default_terms,
            'default_notes'           => $this->default_notes,
        ]);

        PurchaseOrderSetting::clearCache();

        session()->flash('success', 'Purchase Order settings saved.');
        $this->dispatch('purchase-order-saved');
    }

    public function render()
    {
        return view('livewire.settings.modules.purchase-order', [
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
