<?php

/**
 * Leave Request form, Cancel-vs-Delete taxonomy (see "Destructive
 * actions" in CLAUDE.md). Delete = hard delete, only for a
 * never-submitted draft. Cancel = state transition, for a submitted
 * (pending) request. Mutually exclusive by state.
 */

use App\Livewire\HR\Leave\Requests\Form;
use App\Models\HR\LeaveRequest;
use Livewire\Livewire;

it('hard-deletes a never-submitted draft leave request', function () {
    actAsAdmin();
    $request = LeaveRequest::factory()->create(['status' => 'draft']);

    Livewire::test(Form::class, ['id' => $request->id])
        ->call('delete')
        ->assertRedirect(route('hr.leave.requests.index'));

    expect(LeaveRequest::find($request->id))->toBeNull();
});

it('refuses to delete a submitted request — directs to cancel instead', function () {
    actAsAdmin();

    foreach (['pending', 'approved', 'rejected'] as $status) {
        $request = LeaveRequest::factory()->create(['status' => $status]);

        Livewire::test(Form::class, ['id' => $request->id])
            ->call('delete')
            ->assertNoRedirect();

        expect(LeaveRequest::find($request->id))->not->toBeNull();
    }
});

it('offers Delete (not Cancel) for a draft request', function () {
    actAsAdmin();
    $request = LeaveRequest::factory()->create(['status' => 'draft']);

    Livewire::test(Form::class, ['id' => $request->id])
        ->assertViewHas('canDeleteLeave', true)
        ->assertViewHas('canCancelLeave', false);
});

it('offers Cancel (not Delete) for a pending request', function () {
    actAsAdmin();
    $request = LeaveRequest::factory()->create(['status' => 'pending']);

    Livewire::test(Form::class, ['id' => $request->id])
        ->assertViewHas('canCancelLeave', true)
        ->assertViewHas('canDeleteLeave', false);
});

it('offers neither Cancel nor Delete for approved / rejected / cancelled requests', function () {
    actAsAdmin();

    foreach (['approved', 'rejected', 'cancelled'] as $status) {
        $request = LeaveRequest::factory()->create(['status' => $status]);

        Livewire::test(Form::class, ['id' => $request->id])
            ->assertViewHas('canCancelLeave', false)
            ->assertViewHas('canDeleteLeave', false);
    }
});
