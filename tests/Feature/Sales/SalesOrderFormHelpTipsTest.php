<?php

/**
 * Pilot of the <x-ui.help-tip> inline-help pattern on the Sales Order form.
 * If this breaks because the help-tip component is renamed or removed, the
 * test name is intentionally explicit so the failing line points you at the
 * right place. Don't repurpose this file for general form coverage —
 * blade-presence tests rot fast.
 */

use App\Livewire\Sales\Orders\Form;
use Livewire\Livewire;

it('renders the help-tip text for the four piloted Sales Order form fields', function () {
    actAsAdmin();

    $html = Livewire::test(Form::class)->html();

    expect($html)->toContain('The date your quotation expires');
    expect($html)->toContain('Override product prices for this order');
    expect($html)->toContain('Determines the invoice due date');
    expect($html)->toContain('ships each line item as it becomes available');
});
