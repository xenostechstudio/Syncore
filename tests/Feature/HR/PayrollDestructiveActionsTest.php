<?php

/**
 * Payroll Run form, Cancel-vs-Delete taxonomy (see "Destructive
 * actions" in CLAUDE.md). Delete = hard delete, only for a
 * never-approved draft. Cancel = state transition, for an approved
 * run. Mutually exclusive by state.
 */

use App\Livewire\HR\Payroll\Form;
use App\Models\HR\PayrollPeriod;
use Livewire\Livewire;

it('hard-deletes a never-approved draft payroll run', function () {
    actAsAdmin();
    $period = PayrollPeriod::factory()->create(['status' => 'draft']);

    Livewire::test(Form::class, ['id' => $period->id])
        ->call('delete')
        ->assertRedirect(route('hr.payroll.index'));

    expect(PayrollPeriod::find($period->id))->toBeNull();
});

it('refuses to delete a run that has been approved or beyond', function () {
    actAsAdmin();

    foreach (['approved', 'processing', 'paid'] as $status) {
        $period = PayrollPeriod::factory()->create(['status' => $status]);

        Livewire::test(Form::class, ['id' => $period->id])
            ->call('delete')
            ->assertNoRedirect();

        expect(PayrollPeriod::find($period->id))->not->toBeNull();
    }
});

it('offers Delete (not Cancel) for a draft run', function () {
    actAsAdmin();
    $period = PayrollPeriod::factory()->create(['status' => 'draft']);

    Livewire::test(Form::class, ['id' => $period->id])
        ->assertViewHas('canDeletePayroll', true)
        ->assertViewHas('canCancelPayroll', false);
});

it('offers Cancel (not Delete) for an approved run', function () {
    actAsAdmin();
    $period = PayrollPeriod::factory()->create(['status' => 'approved']);

    Livewire::test(Form::class, ['id' => $period->id])
        ->assertViewHas('canCancelPayroll', true)
        ->assertViewHas('canDeletePayroll', false);
});

it('offers neither Cancel nor Delete once a run is processing / paid / cancelled', function () {
    actAsAdmin();

    foreach (['processing', 'paid', 'cancelled'] as $status) {
        $period = PayrollPeriod::factory()->create(['status' => $status]);

        Livewire::test(Form::class, ['id' => $period->id])
            ->assertViewHas('canCancelPayroll', false)
            ->assertViewHas('canDeletePayroll', false);
    }
});
