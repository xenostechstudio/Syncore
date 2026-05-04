<?php

use App\Livewire\HR\Attendance\Schedules\Index;
use App\Models\HR\WorkSchedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create()->assignRole('super-admin'));
});

function attachEmployeeSchedule(int $workScheduleId): int
{
    $employeeId = DB::table('employees')->insertGetId([
        'name' => fake()->name(),
        'employment_type' => 'permanent',
        'status' => 'active',
        'basic_salary' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return DB::table('employee_schedules')->insertGetId([
        'employee_id' => $employeeId,
        'work_schedule_id' => $workScheduleId,
        'effective_from' => now()->toDateString(),
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Work Schedules Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('sort', 'name_asc')
            ->assertSet('search', '')
            ->assertSet('status', '');
    });

    it('renders list', function () {
        WorkSchedule::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('schedules', fn ($p) => $p->total() === 3);
    });

    it('filters by search', function () {
        WorkSchedule::factory()->create(['name' => 'Morning', 'code' => 'MRG']);
        WorkSchedule::factory()->create(['name' => 'Night', 'code' => 'NGT']);

        Livewire::test(Index::class)
            ->set('search', 'Morning')
            ->assertViewHas('schedules', fn ($p) => $p->total() === 1);
    });

    it('toggleStatus flips is_active', function () {
        $s = WorkSchedule::factory()->create(['is_active' => true]);

        Livewire::test(Index::class)->call('toggleStatus', $s->id);

        expect($s->fresh()->is_active)->toBeFalse();
    });

    it('confirmBulkDelete separates schedules with and without employee assignments', function () {
        $empty = WorkSchedule::factory()->create(['name' => 'Unused']);
        $used = WorkSchedule::factory()->create(['name' => 'Used']);
        attachEmployeeSchedule($used->id);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $used->id])
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['canDelete'][0]['name'])->toBe('Unused');
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['name'])->toBe('Used');
    });

    it('bulkDelete removes only unused schedules', function () {
        $empty = WorkSchedule::factory()->create();
        $used = WorkSchedule::factory()->create();
        attachEmployeeSchedule($used->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $used->id])
            ->call('bulkDelete');

        expect(WorkSchedule::find($empty->id))->toBeNull();
        expect(WorkSchedule::find($used->id))->not->toBeNull();
    });
});
