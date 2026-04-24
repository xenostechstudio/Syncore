<?php

use App\Livewire\HR\Employees\Index;
use App\Models\HR\Department;
use App\Models\HR\Employee;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

describe('Employees Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('search', '')
            ->assertSet('departmentId', '')
            ->assertSet('status', '');
    });

    it('renders list', function () {
        Employee::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('employees', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name or email', function () {
        Employee::factory()->create(['name' => 'Alice Smith']);
        Employee::factory()->create(['name' => 'Bob Jones']);

        Livewire::test(Index::class)
            ->set('search', 'Alice')
            ->assertViewHas('employees', fn ($p) => $p->total() === 1);
    });

    it('filters by departmentId and status', function () {
        $dept = Department::factory()->create();
        Employee::factory()->create(['department_id' => $dept->id, 'status' => 'active']);
        Employee::factory()->count(2)->terminated()->create();
        Employee::factory()->create();

        Livewire::test(Index::class)
            ->set('departmentId', (string) $dept->id)
            ->assertViewHas('employees', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('status', 'terminated')
            ->assertViewHas('employees', fn ($p) => $p->total() === 2);
    });

    it('confirmBulkDelete allows only terminated or resigned employees', function () {
        $active = Employee::factory()->create(['name' => 'Active']);
        $terminated = Employee::factory()->terminated()->create(['name' => 'Terminated']);
        $resigned = Employee::factory()->resigned()->create(['name' => 'Resigned']);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $active->id, (string) $terminated->id, (string) $resigned->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(2);
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['name'])->toBe('Active');
    });

    it('bulkDelete removes only terminated/resigned employees', function () {
        $active = Employee::factory()->create();
        $terminated = Employee::factory()->terminated()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $active->id, (string) $terminated->id])
            ->call('bulkDelete');

        expect(Employee::withTrashed()->find($active->id))->not->toBeNull();
        expect(Employee::find($terminated->id))->toBeNull();
    });

    it('bulkUpdateStatus updates selected employees', function () {
        $employees = Employee::factory()->count(2)->create();

        Livewire::test(Index::class)
            ->set('selected', $employees->pluck('id')->map(fn ($id) => (string) $id)->toArray())
            ->call('bulkUpdateStatus', 'on_leave');

        expect(Employee::where('status', 'on_leave')->count())->toBe(2);
    });
});
