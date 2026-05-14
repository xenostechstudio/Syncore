<?php

/**
 * Employee follows the master-data Archive pattern (see "Destructive
 * actions" in CLAUDE.md). Like Lead it's the hybrid shape — a radio
 * status filter gains an "Archived" option and the selection toolbar
 * offers bulkRestore. The index's list branch also renders whenever
 * status === 'archived' so archived rows never reach the kanban/grid
 * card links (which would 404 on a soft-deleted model).
 */

use App\Livewire\HR\Employees\Form;
use App\Livewire\HR\Employees\Index;
use App\Models\HR\Employee;
use Livewire\Livewire;

it('archives an employee as a recoverable soft delete (not a hard delete)', function () {
    actAsAdmin();
    $employee = Employee::factory()->create();

    Livewire::test(Form::class, ['id' => $employee->id])
        ->call('archive')
        ->assertRedirect(route('hr.employees.index'));

    expect(Employee::find($employee->id))->toBeNull();
    expect(Employee::withTrashed()->find($employee->id))->not->toBeNull();
    expect(Employee::withTrashed()->find($employee->id)->trashed())->toBeTrue();
});

it('hides archived employees by default, shows them under the Archived status filter', function () {
    actAsAdmin();
    $live = Employee::factory()->create(['name' => 'Live Employee '.uniqid()]);
    $archived = Employee::factory()->create(['name' => 'Archived Employee '.uniqid()]);
    $archived->delete();

    Livewire::test(Index::class)
        ->assertSee($live->name)
        ->assertDontSee($archived->name);

    Livewire::test(Index::class)
        ->set('status', 'archived')
        ->assertSee($archived->name)
        ->assertDontSee($live->name);
});

it('restores archived employees via bulkRestore (list selection)', function () {
    actAsAdmin();
    $a = Employee::factory()->create();
    $b = Employee::factory()->create();
    $a->delete();
    $b->delete();

    expect(Employee::whereIn('id', [$a->id, $b->id])->count())->toBe(0);

    Livewire::test(Index::class)
        ->set('status', 'archived')
        ->set('selected', [$a->id, $b->id])
        ->call('bulkRestore');

    expect(Employee::whereIn('id', [$a->id, $b->id])->count())->toBe(2);
});

it('restores a single archived employee via restore', function () {
    actAsAdmin();
    $employee = Employee::factory()->create();
    $employee->delete();

    expect(Employee::find($employee->id))->toBeNull();

    Livewire::test(Index::class)
        ->set('status', 'archived')
        ->call('restore', $employee->id);

    expect(Employee::find($employee->id))->not->toBeNull();
});
