<?php

use App\Livewire\HR\Positions\Index;
use App\Models\HR\Department;
use App\Models\HR\Position;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

function attachEmployeeToPosition(int $positionId): int
{
    return DB::table('employees')->insertGetId([
        'name' => fake()->name(),
        'position_id' => $positionId,
        'employment_type' => 'permanent',
        'status' => 'active',
        'basic_salary' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Positions Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('sort', 'name_asc')
            ->assertSet('view', 'grid')
            ->assertSet('departmentId', '');
    });

    it('renders list', function () {
        Position::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('positions', fn ($p) => $p->total() === 3);
    });

    it('filters by search', function () {
        Position::factory()->create(['name' => 'Engineer']);
        Position::factory()->create(['name' => 'Designer']);

        Livewire::test(Index::class)
            ->set('search', 'Engin')
            ->assertViewHas('positions', fn ($p) => $p->total() === 1);
    });

    it('filters by departmentId', function () {
        $d1 = Department::factory()->create();
        $d2 = Department::factory()->create();
        Position::factory()->create(['department_id' => $d1->id]);
        Position::factory()->count(2)->create(['department_id' => $d2->id]);

        Livewire::test(Index::class)
            ->set('departmentId', (string) $d1->id)
            ->assertViewHas('positions', fn ($p) => $p->total() === 1);
    });

    it('filters by active/inactive status', function () {
        Position::factory()->create();
        Position::factory()->count(2)->inactive()->create();

        Livewire::test(Index::class)
            ->set('status', 'inactive')
            ->assertViewHas('positions', fn ($p) => $p->total() === 2);
    });

    it('confirmBulkDelete splits positions by employee count', function () {
        $empty = Position::factory()->create(['name' => 'Empty']);
        $filled = Position::factory()->create(['name' => 'Filled']);
        attachEmployeeToPosition($filled->id);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $filled->id])
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['reason'])->toBe('Has 1 employees');
    });

    it('bulkDelete removes only positions without employees', function () {
        $empty = Position::factory()->create();
        $filled = Position::factory()->create();
        attachEmployeeToPosition($filled->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $filled->id])
            ->call('bulkDelete');

        expect(Position::find($empty->id))->toBeNull();
        expect(Position::find($filled->id))->not->toBeNull();
    });
});
