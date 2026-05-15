<?php

/**
 * Master-data hard Delete: a true `forceDelete()`, distinct from the
 * recoverable Archive, allowed only when no HR records (leave,
 * attendance, payroll, schedules, salary components, or reports)
 * reference the employee. Otherwise the employee must be Archived. See
 * "Destructive actions" in CLAUDE.md.
 */

use App\Livewire\HR\Employees\Form;
use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use Livewire\Livewire;

it('hard-deletes an employee with no HR records', function () {
    actAsAdmin();
    $employee = Employee::factory()->create();

    Livewire::test(Form::class, ['id' => $employee->id])
        ->assertSet('canDelete', true)
        ->call('delete')
        ->assertRedirect(route('hr.employees.index'));

    expect(Employee::withTrashed()->find($employee->id))->toBeNull();
});

it('refuses to hard-delete an employee that has attendance records', function () {
    actAsAdmin();
    $employee = Employee::factory()->create();
    Attendance::factory()->create(['employee_id' => $employee->id]);

    Livewire::test(Form::class, ['id' => $employee->id])
        ->assertSet('canDelete', false)
        ->call('delete');

    expect(Employee::find($employee->id))->not->toBeNull();
});
