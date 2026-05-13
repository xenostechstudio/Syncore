<?php

/**
 * Pilot of the <x-ui.help-tip> inline-help pattern on the Sales Order form,
 * plus regression coverage for the per-user enable/disable preference. If
 * this file breaks because the help-tip component is renamed or removed,
 * the failing line points at the right place. Don't repurpose for general
 * form coverage — blade-presence tests rot fast.
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

it('hides every help-tip when the user has show_help_tips = false', function () {
    $user = actAsAdmin();
    $user->update(['show_help_tips' => false]);

    $html = Livewire::test(Form::class)->html();

    // None of the four piloted strings should render.
    expect($html)->not->toContain('The date your quotation expires');
    expect($html)->not->toContain('Override product prices for this order');
    expect($html)->not->toContain('Determines the invoice due date');
    expect($html)->not->toContain('ships each line item as it becomes available');

    // The question-mark-circle icon should also not appear (it's the
    // visual affordance — if any leaked through, the component's @if
    // gate isn't working).
    expect($html)->not->toContain('question-mark-circle');
});
