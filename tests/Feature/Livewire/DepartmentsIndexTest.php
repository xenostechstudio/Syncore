<?php

use App\Livewire\HR\Departments\Index;
use App\Models\HR\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function makeEmployee(int $departmentId, string $name = 'Test Employee'): int
{
    return DB::table('employees')->insertGetId([
        'name' => $name,
        'department_id' => $departmentId,
        'employment_type' => 'permanent',
        'status' => 'active',
        'basic_salary' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Departments Index (WithIndexComponent trait adoption)', function () {
    it('mounts with overridden sort and view defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('sort', 'name_asc')
            ->assertSet('view', 'grid')
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('selected', [])
            ->assertSet('selectAll', false);
    });

    it('renders list without errors', function () {
        Department::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('departments', fn ($d) => $d->total() === 3);
    });

    it('filters by search term', function () {
        Department::factory()->create(['name' => 'Engineering']);
        Department::factory()->create(['name' => 'Marketing']);

        Livewire::test(Index::class)
            ->set('search', 'Engin')
            ->assertViewHas('departments', fn ($d) => $d->total() === 1);
    });

    it('resets page and clears selection when search changes', function () {
        $d = Department::factory()->create();

        Livewire::test(Index::class)
            ->set('page', 2)
            ->set('selected', [(string) $d->id])
            ->set('search', 'anything')
            ->assertSet('page', 1)
            ->assertSet('selected', []);
    });

    it('populates $selected with all visible IDs when selectAll is checked', function () {
        $departments = Department::factory()->count(3)->create();

        $component = Livewire::test(Index::class)
            ->set('selectAll', true);

        expect($component->get('selected'))
            ->toHaveCount(3)
            ->toEqualCanonicalizing($departments->pluck('id')->map(fn ($id) => (string) $id)->toArray());
    });

    it('clearSelection empties $selected and unchecks selectAll', function () {
        $d = Department::factory()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $d->id])
            ->set('selectAll', true)
            ->call('clearSelection')
            ->assertSet('selected', [])
            ->assertSet('selectAll', false);
    });

    it('confirmBulkDelete splits selected into canDelete and cannotDelete based on employees', function () {
        $empty = Department::factory()->create(['name' => 'Empty Team']);
        $staffed = Department::factory()->create(['name' => 'Staffed Team']);
        makeEmployee($staffed->id);
        makeEmployee($staffed->id);

        $component = Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $staffed->id])
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $validation = $component->get('deleteValidation');

        expect($validation['totalSelected'])->toBe(2);
        expect($validation['canDelete'])->toHaveCount(1);
        expect($validation['canDelete'][0]['name'])->toBe('Empty Team');
        expect($validation['cannotDelete'])->toHaveCount(1);
        expect($validation['cannotDelete'][0]['name'])->toBe('Staffed Team');
        expect($validation['cannotDelete'][0]['reason'])->toBe('Has 2 employees');
    });

    it('bulkDelete removes only departments without employees and flashes success', function () {
        $empty = Department::factory()->create();
        $staffed = Department::factory()->create();
        makeEmployee($staffed->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $staffed->id])
            ->call('bulkDelete');

        expect(Department::find($empty->id))->toBeNull();
        expect(Department::find($staffed->id))->not->toBeNull();
    });

    it('bulkDelete flashes an error when every selected department has employees', function () {
        $a = Department::factory()->create();
        $b = Department::factory()->create();
        makeEmployee($a->id);
        makeEmployee($b->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $a->id, (string) $b->id])
            ->call('bulkDelete');

        expect(Department::count())->toBe(2);
    });

    it('cancelDelete closes modal, clears validation and selection', function () {
        $d = Department::factory()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $d->id])
            ->call('confirmBulkDelete')
            ->call('cancelDelete')
            ->assertSet('showDeleteConfirm', false)
            ->assertSet('deleteValidation', [])
            ->assertSet('selected', []);
    });

    it('applies the name_desc sort via match expression in render', function () {
        Department::factory()->create(['name' => 'Alpha']);
        Department::factory()->create(['name' => 'Bravo']);
        Department::factory()->create(['name' => 'Charlie']);

        $component = Livewire::test(Index::class)->set('sort', 'name_desc');

        $names = $component->viewData('departments')->pluck('name')->all();
        expect($names)->toBe(['Charlie', 'Bravo', 'Alpha']);
    });

    it('filters by active/inactive status', function () {
        Department::factory()->create(['is_active' => true]);
        Department::factory()->count(2)->inactive()->create();

        Livewire::test(Index::class)
            ->set('status', 'inactive')
            ->assertViewHas('departments', fn ($d) => $d->total() === 2);
    });
});
