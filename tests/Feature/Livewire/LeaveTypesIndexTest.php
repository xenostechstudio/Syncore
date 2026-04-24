<?php

use App\Livewire\HR\Leave\Types\Index;
use App\Models\HR\LeaveType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function attachLeaveRequest(int $leaveTypeId): int
{
    $employeeId = DB::table('employees')->insertGetId([
        'name' => fake()->name(),
        'employment_type' => 'permanent',
        'status' => 'active',
        'basic_salary' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return DB::table('leave_requests')->insertGetId([
        'employee_id' => $employeeId,
        'leave_type_id' => $leaveTypeId,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDay()->toDateString(),
        'days' => 1,
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

describe('Leave Types Index', function () {
    it('mounts with correct defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('sort', 'name_asc')
            ->assertSet('search', '')
            ->assertSet('status', '')
            ->assertSet('selected', []);
    });

    it('renders list of leave types', function () {
        LeaveType::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('leaveTypes', fn ($p) => $p->total() === 3);
    });

    it('filters by search on name or code', function () {
        LeaveType::factory()->create(['name' => 'Annual Leave', 'code' => 'ANN']);
        LeaveType::factory()->create(['name' => 'Sick Leave', 'code' => 'SICK']);

        Livewire::test(Index::class)
            ->set('search', 'Annual')
            ->assertViewHas('leaveTypes', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('search', 'SICK')
            ->assertViewHas('leaveTypes', fn ($p) => $p->total() === 1);
    });

    it('filters by active/inactive status', function () {
        LeaveType::factory()->create();
        LeaveType::factory()->count(2)->inactive()->create();

        Livewire::test(Index::class)
            ->set('status', 'inactive')
            ->assertViewHas('leaveTypes', fn ($p) => $p->total() === 2);
    });

    it('sorts by days_high and days_low', function () {
        LeaveType::factory()->create(['name' => 'A', 'days_per_year' => 5]);
        LeaveType::factory()->create(['name' => 'B', 'days_per_year' => 20]);
        LeaveType::factory()->create(['name' => 'C', 'days_per_year' => 12]);

        $high = Livewire::test(Index::class)->set('sort', 'days_high')
            ->viewData('leaveTypes')->pluck('days_per_year')->all();
        expect($high)->toBe([20, 12, 5]);

        $low = Livewire::test(Index::class)->set('sort', 'days_low')
            ->viewData('leaveTypes')->pluck('days_per_year')->all();
        expect($low)->toBe([5, 12, 20]);
    });

    it('selectAll populates $selected', function () {
        $types = LeaveType::factory()->count(3)->create();

        $c = Livewire::test(Index::class)->set('selectAll', true);
        expect($c->get('selected'))->toHaveCount(3);
    });

    it('confirmBulkDelete separates types with and without requests', function () {
        $empty = LeaveType::factory()->create(['name' => 'Empty']);
        $used = LeaveType::factory()->create(['name' => 'Used']);
        attachLeaveRequest($used->id);

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $used->id])
            ->call('confirmBulkDelete')
            ->assertSet('showDeleteConfirm', true);

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect($v['canDelete'][0]['name'])->toBe('Empty');
        expect($v['cannotDelete'])->toHaveCount(1);
        expect($v['cannotDelete'][0]['reason'])->toBe('Has 1 leave requests');
    });

    it('bulkDelete removes only leave types without requests', function () {
        $empty = LeaveType::factory()->create();
        $used = LeaveType::factory()->create();
        attachLeaveRequest($used->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $empty->id, (string) $used->id])
            ->call('bulkDelete');

        expect(LeaveType::find($empty->id))->toBeNull();
        expect(LeaveType::find($used->id))->not->toBeNull();
    });

    it('bulkDelete returns early when everything is blocked', function () {
        $a = LeaveType::factory()->create();
        $b = LeaveType::factory()->create();
        attachLeaveRequest($a->id);
        attachLeaveRequest($b->id);

        Livewire::test(Index::class)
            ->set('selected', [(string) $a->id, (string) $b->id])
            ->call('bulkDelete');

        expect(LeaveType::count())->toBe(2);
    });
});
