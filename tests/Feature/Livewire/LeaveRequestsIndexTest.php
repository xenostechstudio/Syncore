<?php

use App\Livewire\HR\Leave\Requests\Index;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveType;
use App\Models\User;
use Database\Seeders\ModulePermissionSeeder;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

beforeEach(function () {
    Event::fake([
        App\Events\LeaveRequestApproved::class,
        App\Events\LeaveRequestRejected::class,
    ]);
    $this->seed(ModulePermissionSeeder::class);
    $user = User::factory()->create()->assignRole('super-admin');
    $user->assignRole('super-admin');
    $this->actingAs($user);
});

describe('LeaveRequests Index', function () {
    it('mounts with defaults', function () {
        Livewire::test(Index::class)
            ->assertSet('status', '')
            ->assertSet('leaveTypeId', '');
    });

    it('renders list', function () {
        LeaveRequest::factory()->count(3)->create();

        Livewire::test(Index::class)
            ->assertStatus(200)
            ->assertViewHas('requests', fn ($p) => $p->total() === 3);
    });

    it('filters by status and leaveTypeId', function () {
        $lt = LeaveType::factory()->create();
        LeaveRequest::factory()->create(['leave_type_id' => $lt->id, 'status' => 'pending']);
        LeaveRequest::factory()->count(2)->approved()->create();

        Livewire::test(Index::class)
            ->set('leaveTypeId', (string) $lt->id)
            ->assertViewHas('requests', fn ($p) => $p->total() === 1);

        Livewire::test(Index::class)
            ->set('status', 'approved')
            ->assertViewHas('requests', fn ($p) => $p->total() === 2);
    });

    it('confirmBulkDelete blocks approved requests', function () {
        $pending = LeaveRequest::factory()->create();
        $approved = LeaveRequest::factory()->approved()->create();

        $c = Livewire::test(Index::class)
            ->set('selected', [(string) $pending->id, (string) $approved->id])
            ->call('confirmBulkDelete');

        $v = $c->get('deleteValidation');
        expect($v['canDelete'])->toHaveCount(1);
        expect((int) $v['canDelete'][0]['id'])->toBe($pending->id);
        expect($v['cannotDelete'])->toHaveCount(1);
    });

    it('bulkDelete removes only non-approved requests', function () {
        $pending = LeaveRequest::factory()->create();
        $approved = LeaveRequest::factory()->approved()->create();
        $rejected = LeaveRequest::factory()->rejected()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $pending->id, (string) $approved->id, (string) $rejected->id])
            ->call('bulkDelete');

        expect(LeaveRequest::find($pending->id))->toBeNull();
        expect(LeaveRequest::find($approved->id))->not->toBeNull();
        expect(LeaveRequest::find($rejected->id))->toBeNull();
    });

    it('approveSelected moves pending to approved', function () {
        $a = LeaveRequest::factory()->create();
        $b = LeaveRequest::factory()->create();
        $approved = LeaveRequest::factory()->approved()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $a->id, (string) $b->id, (string) $approved->id])
            ->call('approveSelected');

        expect($a->fresh()->status)->toBe('approved');
        expect($b->fresh()->status)->toBe('approved');
    });

    it('rejectSelected marks pending as rejected', function () {
        $a = LeaveRequest::factory()->create();

        Livewire::test(Index::class)
            ->set('selected', [(string) $a->id])
            ->call('rejectSelected');

        expect($a->fresh()->status)->toBe('rejected');
    });
});
